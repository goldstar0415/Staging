<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Role;
use App\Services\AppSettings;
use App\Spot;
use App\SpotTypeCategory;
use App\SpotVote;
use App\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use MongoDB\Driver\Manager as MongoClient;
use MongoDB\Driver\Query;
use MongoCollection;
use Validator;

class CrawlerRun extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var AppSettings
     */
    protected $settings = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
//    public function __construct()
//    {
//
//    }

    /**
     * Execute the job.
     *
     * @param AppSettings $settings
     */
    public function handle(AppSettings $settings)
    {
        $crawler = $settings->crawler;
        $skip = isset($crawler->last_imported_row) ? $crawler->last_imported_row : 0;

        $mongo = new MongoClient('mongodb://54.174.50.110:27017');
        $query = new Query([]);
        $cursor = $mongo->executeQuery('airbnb.apartments', $query);
        //$db = $mongo->selectDB('airbnb');
        //$coll = new MongoCollection($db, 'apartments');
        //$cursor = $coll->find();
        if ($skip !== 0) {
            $cursor = $cursor->skip($skip);
        }

        $category = SpotTypeCategory::whereName('air_bnb')->first();
        $i = $skip;
        foreach ($cursor as $item) {
            ++$i;

            $data = [
                'title' => $item['name'],
                'description' => implode(',<br />', [
                    'Person capacity: ' . $item['person_capacity'],
                    'Property type: ' . $item['property_type'],
                    'Room type: ' . $item['room_type']
                ]),
                'cover' => $item['xl_picture_url']
            ];
            $point = [
                'address' => $item['public_address'],
                'location' => [
                    'lat' => (int)$item['lat'],
                    'lng' => (int)$item['lng']
                ]
            ];
            $rating = (int)$item['star_rating'];
            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'string|max:5000',
                'address' => 'required|string|max:255',
                'location.lat' => 'required|numeric',
                'location.lng' => 'required|numeric',
                'rating' => 'integer',
                'cover' => 'remote_image'
            ];
            $validator = Validator::make($data + $point + ['rating' => $rating], $rules);
            if ($validator->fails()) {
                continue;
            }

            $spot = new Spot($data);
            $spot->is_approved = true;
            $spot->category()->associate($category);
            $spot->save();

            $spot->locations = [$point];

            $vote = new SpotVote(['vote' => $rating]);
            $vote->user()->associate(Role::take('admin')->users()->first());
            $spot->votes()->save($vote);
            foreach ($item['xl_picture_urls'] as $picture_url) {
                if (!Validator::make(['picture' => $picture_url], ['picture' => 'remote_image'])->fails()) {
                    $spot->photos()->create([
                        'photo' => $picture_url
                    ]);
                }
            }
        }
        $crawler->last_imported_row = $i;
        $settings->crawler = $crawler;
    }
}
