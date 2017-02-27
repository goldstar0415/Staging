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
     * @var integer
     */
    public $page;
    
    /**
     * @var string
     */
    public $imageUrlPattern = '/^(https?:\/\/(s1\.ticketm\.net\/)(dam|dbimages)\/)([^\.][\w\/\-]+)?\.(jpg|png)?$/';
    
    public $picKeyReplacements = [
        '_TABLET',
        '_LANDSCAPE',
        '_RECOMENDATION',
        '_RETINA',
        '_CUSTOM',
        '_RETINA',
        '_PORTRAIT',
        '_LARGE',
        '_ARTIST_PAGE',
        '_EVENT_DETAIL_PAGE',
        '_3_2',
        '_16_9',
        '_4_3',
    ];

    /**
     * Execute the job.
     *
     * @param Client $http
     */
    public function handle(Client $http)
    {
        $this->http = $http;
        $this->settings = config('ticket-master');
        $page = !empty($this->page)?$this->page:1;
        //Log::info('$page = ' . $page);
        $query_string = ['apikey' => $this->settings['apikey'] , 'size' => 500, 'page' => $page];
        $data = [];
        $data = $this->fetchData($query_string);
        $nextPage = $data['page']['number']+1;
        //Log::info('nextPage = ' . $nextPage);
        $events = collect($data['_embedded']['events']);
        //dd(json_encode($events));
        $this->importEvents($events);
        
        // comment it if you want to do all job in one queue
        if($nextPage <= 5) //may set all pages instead of 5
        {
            $newJob = (new TicketMasterEvents);
            $newJob->page = $nextPage;
            dispatch($newJob);
        }
    }

    public function importEvents(Collection $events)
    {
        $default_category = SpotTypeCategory::whereName('ticketmaster')->first();
        foreach ($events as $event) {
            if(empty($event['id']) || empty($event['name']) ||  Spot::where('spot_type_category_id', $default_category->id)->where('remote_id', $event['id'])->exists())
            {
                continue;
            }
            $import_event = new Spot();
            $import_event->category()->associate($default_category);
            $import_event->title = (!empty($event['name']))?$event['name']:null;
            
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
            $import_event->is_private = false;
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
	 * Get remote photos
	 * @param $event
	 * @return array
	 */
    protected function getRemotePhotos($event)
    {
        $remotePhotos = [];
        $needCover = true;
        $images = $event['images'];
        
        $imagesArr = [];
        
        foreach($images as $image)
        {
            preg_match($this->imageUrlPattern, $image['url'], $matchArr);
            if( !isset($matchArr[3]))
            {
                $imagesArr[] = [
                    'url' => $image['url'],
                    'width' => isset($image['width'])?$image['width']:0,
                ];
            }
            else 
            {
                $key = ($matchArr[3] == 'dam')?str_replace($this->picKeyReplacements, '', $matchArr[4]):$matchArr[4];
                if( !isset($imagesArr[$key]) || (isset($imagesArr[$key]) && $image['width'] > $imagesArr[$key]['width']))
                {
                    $imagesArr[$key] = [
                        'url' => $image['url'],
                        'width' => isset($image['width'])?$image['width']:0,
                    ];
                }
            }
        }
        if(!empty($imagesArr))
        {
            foreach($imagesArr as $imageArr)
            {
                $isCover = 0;
                if($needCover)
                {
                    $isCover = 1;
                    $needCover = false;
                }
                $remotePhotos[] = new RemotePhoto([
                    'url' => $imageArr['url'],
                    'image_type' => $isCover, // 1 - cover, 0 - regular
                    'size' => 'original',
                ]);
            }
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
