<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\RemotePhoto;
use App\Services\AppSettings;
use App\Spot;
use App\SpotTypeCategory;
use GuzzleHttp\Client;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class TicketMasterEvents extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var Client
     */
    private $http;

    /**
     * @var AppSettings
     */
    private $config;

    /**
     * @var integer
     */
    public $page;
    
    /**
     * @var string
     */
    public $imageUrlPattern = '/^(https?:\/\/(s1\.ticketm\.net\/)(dam|dbimages)\/)([^\.][\w\/\-]+)?\.(jpg|png)?$/';
    
    public $prefix = null;
    public $category = null;
    
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
    
    public function __construct() {
        $this->config = config('services.ticketmaster');
        $this->category = SpotTypeCategory::getOrCreate('ticketmaster', 'TicketMaster');
        $this->prefix = $this->category->getPrefix();
    }

    /**
     * Execute the job.
     *
     * @param Client $http
     */
    public function handle(Client $http)
    {
        $this->http = $http;
        $page = !empty($this->page)?$this->page:1; // setting page number to 1 as default
        $query_string = ['apikey' => $this->config['api_key'] , 'size' => 500, 'page' => $page];
        $data = $this->fetchData($query_string);
        $nextPage = $data['page']['number']+1;
        $events = collect($data['_embedded']['events']);
        $this->importEvents($events);
        
        // comment it if you want to do all job in one queue
        if($nextPage <= $data['page']['totalPages'])
        {
            $newJob = (new TicketMasterEvents);
            $newJob->page = $nextPage;
            dispatch($newJob);
        }
    }

    public function importEvents(Collection $events)
    {
        foreach ($events as $event) {
            if(empty($event['id']) || // No remote Id
               empty($event['name']) || // No title
               Spot::where('spot_type_category_id', $this->category->id) // Spot exists
                    ->where('remote_id', $this->prefix . $event['id'])
                    ->exists())
            {
                continue;
            }
            $import_event = new Spot();
            $import_event->category()->associate($this->category);
            $import_event->title = $event['name'];
            $import_event->remote_id = $this->prefix . $event['id'];
            $import_event->description = (isset($event['info']))?$event['info']:null;
            $import_event->web_sites = $this->getWebSites($event);
            $import_event->is_approved = true;
            $import_event->is_private = false;
            
            // Setting start and end date (there's no end dates so setting to end of a day)
            if(!empty( $event['dates']['start']['localTime']) && !empty( $event['dates']['start']['localDate']))
            {
                $import_event->start_date = $event['dates']['start']['localDate'] . ' ' . $event['dates']['start']['localTime'];
                $import_event->end_date   = $event['dates']['start']['localDate'] . ' ' . '23:59:59';
            }
            
            $import_event->save();
            
            // Address generation
            $this->saveSpotPoint($import_event, $event);

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
        return $sites ? $sites: null;
    }

	/**
	 * Get remote photos
	 * @param $event
	 * @return array
	 */
    protected function getRemotePhotos($event)
    {
        $images = $event['images'];
        $imagesToSave = [];
        
        foreach($images as $image)
        {
            // There's a images magic 
            preg_match($this->imageUrlPattern, $image['url'], $matchArr);
            if( !isset($matchArr[3]))
            {
                // If image have no part "dam" or "dbimages" in url then just adding it
                $imagesToSave[] = [
                    'url' => $image['url'],
                    'width' => isset($image['width'])?$image['width']:0,
                ];
            }
            else 
            {
                // Else if it's "dam" we cleaning url from things that differs and get just image "ID" and then comparing their width
                // If it's "dbimages" we just take one of them because they're similar
                $key = ($matchArr[3] == 'dam')?str_replace($this->picKeyReplacements, '', $matchArr[4]):$matchArr[4];
                if( !isset($imagesArr[$key]) || (isset($imagesArr[$key]) && $image['width'] > $imagesArr[$key]['width']))
                {
                    $imagesToSave[$key] = [
                        'url' => $image['url'],
                        'width' => isset($image['width'])?$image['width']:0,
                    ];
                }
            }
        }
        // Getting array of all images to save
        $remotePhotos = [];
        if(!empty($imagesToSave))
        {
            $needCover = true;
            foreach($imagesToSave as $imageArr)
            {
                $isCover = 0; // 1 - cover, 0 - regular
                if($needCover)
                {
                    $isCover = 1;
                    $needCover = false;
                }
                $remotePhotos[] = new RemotePhoto([
                    'url' => $imageArr['url'],
                    'image_type' => $isCover, 
                    'size' => 'original',
                ]);
            }
        }
        return $remotePhotos;
    }
    
    /**
     * @param Spot $spot
     * @param array $event
     * @return mixed
     */
    public function saveSpotPoint($spot, $event)
    {
        if (!empty($event['_embedded']['venues'][0])) {
            $venue = $event['_embedded']['venues'][0];
            $address = [];
            $lat     = null;
            $lon     = null;
            // Getting full address
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
            // Getting lat and lon
            if( !empty($venue['location']) )
            {
                $lat = $venue['location']['latitude'];
                $lon = $venue['location']['longitude'];
            }
            // If everything ok => saving
            if( !empty($address) && !empty($lat) && !empty($lon) )
            {
                $spot->points()->create([
                    'address' => $address,
                    'location' => [
                        'lat' => $lat,
                        'lng' => $lon
                    ]
                ]);
            }
        }
    }
    
    /**
     * @param $query_string
     * @return array
     */
    public function fetchData($query_string)
    {
        try {
            $response = $this->http->get($this->config['baseUri'], [
                'query' => $query_string
            ]);
            $data = json_decode((string)$response->getBody(), true);
        } catch (Exception $e) {
            $data = [];
        }
        return $data;
    }
}
