<?php

use App\Spot;
use App\SpotPoint;
use App\SpotTypeCategory;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SpotTest extends LaravelTestCase
{
    use DatabaseTransactions;

    public function testSpotCreate()
    {
        $this->randomSignIn();
        /**
         * @var Spot $spot
         */
        $spot = factory(Spot::class)->make();
        $spot_points = factory(SpotPoint::class, mt_rand(1, 10))->make();
        $spot_category = SpotTypeCategory::random()->with('type')->first();
        $data = [
            'title' => $spot->title,
            'spot_type_category_id' => $spot_category->id,
            'description' => $spot->description,
            'web_sites' => $spot->web_sites,
            'videos' => $spot->videos,
            'locations' => $spot_points->toArray(),
            'tags' => ['rem', 'tempora', 'some', 'newtag']
        ];

        if ($spot_category->type->name === 'event') {
            $data = array_merge($data, ['start_date' => $spot->start_date, 'end_date' => $spot->end_date]);
        }

        $response = $this->post('/spots', $data, [], [
            'files' => [$this->makeUploadedFile(), $this->makeUploadedFile(), $this->makeUploadedFile()]
        ]);

        $this->seeJson($spot->toArray());
        $this->assertResponseOk();
    }
}
