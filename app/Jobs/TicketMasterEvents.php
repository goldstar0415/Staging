<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\RemotePhoto;
use App\Services\AppSettings;
use App\Services\GoogleAddress;
use App\Spot;
use App\SpotPhoto;
use App\SpotTypeCategory;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Storage;
use Log;

class TicketMasterEvents extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var Client
     */
    private $http;

    /**
     * @var string
     */
    protected $api_url = 'https://app.ticketmaster.com/discovery/v2/events.json';

    /**
     * @var AppSettings
     */
    private $settings;

    /**
     * @var GoogleAddress
     */
    private $google_address = null;
    
    /**
     * @var integer
     */
    public $page;

    /**
     * Execute the job.
     *
     * @param Client $http
     * @param GoogleAddress $address
     */
    public function handle(Client $http)
    {
        $this->http = $http;
        $this->settings = config('ticket-master');
        $page = !empty($this->page)?$this->page:1;
        $query_string = ['apikey' => $this->settings['apikey'] , 'size' => 500, 'page' => $page];
        $data = [];
        $data = $this->fetchData($query_string);
        $pages_count = $data['page']['totalPages'];
        $nextPage = $data['page']['number']+1;
        $events = collect($data['_embedded']['events']);
        $this->importEvents($events);
        
        // comment it if you want to do all job in one queue
        if($nextPage <= 5) //may set all pages instead of 5: $pages_count
        {
            Log::info('$nextPage <= 5');
            $newJob = (new TicketMasterEvents);
            $newJob->page = $nextPage;
            dispatch($newJob);
        }

        // uncomment it if you want to do all job in one queue
        /*for ($nextPage; $nextPage <=  5 ; $nextPage++) //may set all pages instead of 5: $pages_count
        {
            $query_string['page'] = $nextPage;
            $data = $this->fetchData($query_string);
            $events = collect($data['_embedded']['events']);
            $this->importEvents($events);
        }*/


    }

    public function importEvents(Collection $events)
    {
        $default_category = SpotTypeCategory::whereName('ticketmaster')->first();

        foreach ($events as $event) {
            
            if(Spot::where('spot_type_category_id', $default_category->id)->where('remote_id', $event['id'])->exists())
            {
                continue;
            }

            $import_event = new Spot();
            $import_event->category()->associate($default_category);
            $import_event->title = $event['name'];
            $import_event->remote_id = $event['id'];
            
            $time = !empty( $event['dates']['start']['localTime'])?$event['dates']['start']['localTime']:'08:00:00';
            $date = !empty( $event['dates']['start']['localDate'])?$event['dates']['start']['localDate']:date('Y-m-d');
            $import_event->start_date = $date . ' ' . $time;
            $import_event->end_date = $date . ' 23:59:59';
            
            // Description
            $descriptionArray = [];
            if(isset($event['info']))
            {
                $descriptionArray[] = $event['info'];
            }
            if(isset($event['_embedded']['venues'][0]['name']))
            {
                $venueName = $event['_embedded']['venues'][0]['name'];
                if(isset($event['_embedded']['venues'][0]['url']))
                {
                    $venueName = '<a href="' . $event['_embedded']['venues'][0]['url'] . '">' . $venueName . '</a>';
                }
                $descriptionArray[] = $venueName;
            }
            $descriptionArray[] = $import_event->start_date;
            $import_event->description = implode("\n", $descriptionArray);
            
            $import_event->web_sites = $this->getWebSites($event);
            $import_event->is_approved = true;
            $import_event->save();
            
            // Address generation
            $address = '';
            $lat     = '';
            $lon     = '';
            if (!empty($event['_embedded']['venues'][0])) {
                $venue = $event['_embedded']['venues'][0];

                $address = [];
                if( !empty($venue['address']) )
                {
                    $address[] = implode(', ', $venue['address']);
                }
                if( !empty($venue['city']['name']) )
                {
                    $address[] = $venue['city']['name'];
                }
                if( !empty($venue['state']['name']) )
                {
                    $address[] = $venue['state']['name'];
                }
                
                $address = implode(', ', $address);
                
                if( !empty($venue['location']) )
                {
                    $lat = $venue['location']['latitude'];
                    $lon = $venue['location']['longitude'];
                }
            }
            if( !empty($address) && !empty($lat) && !empty($lon) )
            {
                $import_event->points()->create([
                    'address' => $address,
                    'location' => [
                        'lat' => $lat,
                        'lng' => $lon
                    ]
                ]);
            }

            // Pictures
            if( !empty($event['images']) )
            {
                $import_event->remotePhotos()->saveMany( $this->getRemotePhotos($event) );
            }
        }
        return true;
    }

    protected function getWebSites($event)
    {
        $sites = [];

        if (!empty($event['url'])) {
            $sites[] = $event['url'];
        }

        if (!empty($event['_embedded']['venues'][0]['url'])) {
            $sites[] = $event['_embedded']['venues'][0]['url'];
        }

        return $sites ?: null;
    }

    /**
     * Get photos from imported event
     *
     * @param $event
     * @return array
	 * @deprecated
     */
    protected function getPhotos($event)
    {
        $photos = [];
        foreach ($event['performers'] as $performer) {
            if ($performer['image']) {
                $photos[] = new SpotPhoto(['photo' => $performer['image']]);
            }
        }

        return $photos;
    }

	/**
	 * Get remote photos
	 * @param $event
	 * @return array
	 */
    protected function getRemotePhotos($event)
    {
        $remotePhotos = [];
        $needCover = true;
        $images = $event['images'];
        $cover = array_values(array_filter($images, function($imageArray) {
            return strpos($imageArray['url'], 'http://s1.ticketm.net/dbimages/')!== false?true:false ;
        }));
        if( !empty($cover) )
        {
            $remotePhotos[] = new RemotePhoto([
                'url' => $cover[0]['url'],
                'image_type' => $needCover ? 1 : 0, // 1 - cover, 0 - regular
                'size' => 'original',
            ]);
            $needCover = false;
        }
        $photos = array_values(array_filter($images, function($imageArray) {
            return ((strpos( $imageArray['url'], 'http://s1.ticketm.net/dam/' )!== false?true:false) 
                      && ( $imageArray['width'] < 350 )
                      && ( $imageArray['ratio'] == '4_3' ));
        }));
        if( !empty($photos) )
        {
            $remotePhotos[] = new RemotePhoto([
                'url' => $photos[0]['url'],
                'image_type' => $needCover ? 1 : 0, // 1 - cover, 0 - regular
                'size' => 'original',
            ]);
        }
        return $remotePhotos;
    }

    /**
     * @param $query_string
     * @return mixed
     */
    public function fetchData($query_string)
    {
        $response = $this->http->get($this->api_url, [
            'query' => $query_string
        ]);
        $data = json_decode((string)$response->getBody(), true);

        return $data;
    }
}
