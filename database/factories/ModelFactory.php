<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use Faker\Generator;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\MultiPoint;

$faker = Faker\Factory::create();

$timestamps = function () use ($faker) {
    $created_at = $faker->dateTimeBetween('-2 years');
    $updated_at = clone $created_at;
    $updated_at->modify('+' . mt_rand(1, 100) . ' days');
    return [
        'created_at' => $created_at,
        'updated_at' => $updated_at
    ];
};

$dates = function () use ($faker) {
    $faker = Faker\Factory::create();
    $start_date = $faker->dateTimeBetween('-50 days','+50 days');
    $end_date = clone $start_date;
    $end_date->modify('+' . mt_rand(1, 5) . ' days');
    return [
        'start_date' => $start_date,
        'end_date' => $end_date
    ];
};

$factory->define(App\User::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->email,
        'password' => bcrypt('password'),
        'sex' => $faker->boolean(),
        'avatar' => $faker->imageUrl(),
        'birth_date' => $faker->date(),
        'address' => $faker->address,
        'location' => new Point($faker->latitude, $faker->longitude),
        'time_zone' => $faker->timezone,
        'description' => $faker->sentence,
        'mail_events' => $faker->numberBetween(1, 5),
        'mail_favorites' => $faker->numberBetween(1, 5),
        'mail_followers' => $faker->numberBetween(1, 5),
        'mail_followings' => $faker->numberBetween(1, 5),
        'mail_wall' => $faker->numberBetween(1, 5),
        'mail_info' => $faker->numberBetween(1, 5),
        'mail_photo_map' => $faker->numberBetween(1, 5),
        'notification_letter' => $faker->boolean(),
        'notification_wall_post' => $faker->boolean(),
        'notification_follow' => $faker->boolean(),
        'notification_new_spot' => $faker->boolean(),
        'notification_coming_spot' => $faker->boolean(),
        'remember_token' => str_random(10),
    ], $timestamps());
});

$factory->define(App\Role::class, function (Generator $faker) {
    return [
        'name' => 'zoomer',
        'display_name' => 'Zoomer',
        'description' => 'Zoomtivity User',
    ];
});

$factory->defineAs(App\Role::class, 'admin', function (Generator $faker) {
    return [
        'name' => 'admin',
        'display_name' => 'Administrator',
        'description' => 'Zoomtivity Administrator',
    ];
});

$factory->defineAs(App\Role::class, 'blogger', function (Generator $faker) {
    return [
        'name' => 'blogger',
        'display_name' => 'Blogger',
        'description' => 'Zoomtivity Blogger',
    ];
});

$factory->define(App\Spot::class, function (Generator $faker) use ($timestamps, $dates) {
    $start_date = $faker->dateTimeBetween('-50 days','+50 days');
    $end_date = clone $start_date->modify('+' . mt_rand(1, 5) . ' day');
    return array_merge([
        'title' => $faker->sentence,
        'description' => $faker->sentence,
        'web_site' => $faker->url,
    ], $dates(), $timestamps());
});

$factory->define(App\SpotPoint::class, function (Generator $faker) {
    return [
        'address' => $faker->address,
        'location' => new Point($faker->latitude, $faker->longitude)
    ];
});

$factory->define(App\SpotReview::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'body' => $faker->text
    ], $timestamps());
});

$factory->defineAs(App\SpotType::class, 'event', function (Generator $faker) {
    return [
        'name' => 'event',
        'display_name' => 'Event'
    ];
});

$factory->defineAs(App\SpotType::class, 'recreation', function (Generator $faker) {
    return [
        'name' => 'recreation',
        'display_name' => 'Recreation'
    ];
});

$factory->defineAs(App\SpotType::class, 'pitstop', function (Generator $faker) {
    return [
        'name' => 'pitstop',
        'display_name' => 'Pit Stop'
    ];
});

$factory->define(App\SpotTypeCategory::class, function (Generator $faker) {
    $name = $faker->unique()->word;
    return [
        'name' => $name,
        'display_name' => ucfirst($name)
    ];
});

$factory->define(App\SpotVote::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'vote' => mt_rand(1, 5),
    ], $timestamps());
});

$factory->define(App\Activity::class, function (Generator $faker) use ($timestamps, $dates) {
    return array_merge([
        'title' => $faker->sentence,
        'description' => $faker->sentence(12),
        'address' => $faker->address,
        'location' => new Point($faker->latitude, $faker->longitude),
    ], $dates(), $timestamps());
});

$factory->define(App\ActivityCategory::class, function (Generator $faker) {
    $name = $faker->unique()->word;
    return [
        'name' => $name,
        'display_name' => ucfirst($name)
    ];
});

$factory->define(App\Album::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'name' => $faker->sentence,
        'is_private' => $faker->boolean(),
    ], $timestamps());
});

$factory->define(App\AlbumPhoto::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'address' => $faker->address,
        'location' => new Point($faker->latitude, $faker->longitude),
    ], $timestamps());
});

$factory->define(App\AlbumPhotoComment::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'body' => $faker->sentence(16)
    ], $timestamps());
});

$factory->define(App\Area::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'data' => $faker->text,
        'b_box' => new MultiPoint([
            new Point($faker->latitude, $faker->longitude),
            new Point($faker->latitude, $faker->longitude),
        ])
    ], $timestamps());
});

$factory->define(App\Blog::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'title' => $faker->sentence,
        'body' => $faker->text(300),
        'url' => $faker->url,
        'address' => $faker->address,
        'location' => new Point($faker->latitude, $faker->longitude),
    ], $timestamps());
});

$factory->define(App\BlogCategory::class, function (Generator $faker) {
    $name = $faker->unique()->word;
    return [
        'name' => $name,
        'display_name' => ucfirst($name)
    ];
});

$factory->define(App\BlogComment::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'body' => $faker->sentence(16)
    ], $timestamps());
});

$factory->define(App\BlogComment::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'body' => $faker->sentence(16)
    ], $timestamps());
});

$factory->define(App\BloggerRequest::class, function (Generator $faker) use ($timestamps) {
    $status = ['requested',  'rejected',  'accepted'];
    return array_merge([
        'text' => $faker->text,
        'status' => $status[mt_rand(0, 2)]
    ], $timestamps());
});

$factory->define(App\ChatMessage::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'body' => $faker->sentence(16)
    ], $timestamps());
});

$factory->define(App\Friend::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'birth_date' => $faker->date(),
        'phone' => $faker->phoneNumber,
        'email' => $faker->email,
        'address' => $faker->address,
        'location' => new Point($faker->latitude, $faker->longitude),
        'note' => $faker->sentence
    ], $timestamps());
});

$factory->define(App\Plan::class, function (Generator $faker) use ($timestamps, $dates) {
    return array_merge([
        'title' => $faker->sentence,
        'description' => $faker->sentence,
        'address' => $faker->address,
        'location' => new Point($faker->latitude, $faker->longitude),
    ], $dates(), $timestamps());
});

$factory->define(App\Tag::class, function (Generator $faker) {
    return [
        'name' => $faker->unique()->word
    ];
});

$factory->define(App\Wall::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'body' => $faker->sentence(24),
    ], $timestamps());
});

$factory->define(App\Favorite::class, function (Generator $faker) use ($timestamps) {
    return $timestamps();
});

$factory->define(App\Following::class, function (Generator $faker) use ($timestamps) {
    return $timestamps();
});