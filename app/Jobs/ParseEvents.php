<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Services\AppSettings;
use App\Spot;
use App\SpotTypeCategory;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class ParseEvents extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var Client
     */
    protected $http;

    /**
     * @var string
     */
    protected $api_url = 'http://api.seatgeek.com/2/events';

    /**
     * @var AppSettings
     */
    private $settings;

    /**
     * Create a new job instance.
     * @param Client $http
     * @param AppSettings $settings
     */
    public function __construct(Client $http, AppSettings $settings)
    {
        $this->http = $http;
        $this->settings = $settings;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $query_string = ['sort' => 'id.desc', 'page' => 1, 'per_page' => 1000];
        $parser_settings = $this->settings->parser;
        $data = [];

        if (isset($parser_settings['aid'])) {
            $query_string['aid'] = $this->settings->parser['aid'];
        }

        $pages_count = 0;
        $data = $this->fetchData($query_string);
        $events = collect($data['events']);
        $parser_settings['last_imported_id'] = $events->sortBy('id')->last()['id'];

        $pages_count = ceil($data['meta']['total'] / $data['meta']['per_page']);
        for ($page = 2; $page < $pages_count; ++$page) {
            if (!$this->importEvents($events)) {
                break;
            }
            $query_string['page'] = $page;
            $data = $this->fetchData($query_string);
            $events = collect($data['events']);
        }

        $this->settings->parser = $parser_settings;
    }

    public function importEvents(Collection $events)
    {
        $default_category = SpotTypeCategory::whereName('general')->first();

        foreach ($events->sortByDesc('id') as $event) {
            if (
                isset($this->settings->parser['last_imported_id']) and
                $this->settings->parser['last_imported_id']=== $event['id']
            ) {
                return false;
            }
            $date = DateTime::createFromFormat(DateTime::ISO8601, $event['datetime_utc'] . '+0000');

            $import_event = new Spot();
            $import_event->category()->associate($default_category);
            $import_event->title = $event['title'];
            $import_event->start_date = $date->format('Y-m-d H:i:s');
            $import_event->end_date = $date->format('Y-m-d 23:59:59');
            $import_event->is_approved = true;
            $import_event->save();
            $import_event->points()->create([
                'address' => $this->getEventAddress($event),
                'location' => [
                    'lat' => $event['venue']['location']['lat'],
                    'lng' => $event['venue']['location']['lon']
                ]
            ]);
        }

        return true;
    }

    protected function getEventAddress(array $event)
    {
        if (is_null($event['venue']['address'])) {
            $response = $this->http->get('http://maps.googleapis.com/maps/api/geocode/json', [
                'query' => [
                    'latlng' => $event['venue']['location']['lat'] . ',' . $event['venue']['location']['lon'],
                    'sensor' => true
                ]
            ]);

            $data = json_decode((string)$response->getBody(), true);

            return $data['results'][0]['formatted_address'];
        }

        return $event['venue']['address'];
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
