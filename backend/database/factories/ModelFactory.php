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
        'start_date' => $start_date->format('Y-m-d H:i:s'),
        'end_date' => $end_date->format('Y-m-d H:i:s')
    ];
};

$factory->define(App\User::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->email,
        'password' => bcrypt('password'),
        'sex' => $faker->randomElement(['m', '', 'f']),
        'avatar' => $faker->image(storage_path('app'), 1000, 1000),
        'birth_date' => $faker->date(),
        'address' => $faker->address,
        'time_zone' => $faker->timezone,
        'description' => $faker->sentence,
        'privacy_events' => $faker->numberBetween(1, 5),
        'privacy_favorites' => $faker->numberBetween(1, 5),
        'privacy_followers' => $faker->numberBetween(1, 5),
        'privacy_followings' => $faker->numberBetween(1, 5),
        'privacy_wall' => $faker->numberBetween(1, 5),
        'privacy_info' => $faker->numberBetween(1, 5),
        'privacy_photo_map' => $faker->numberBetween(1, 5),
        'notification_letter' => $faker->boolean(),
        'notification_wall_post' => $faker->boolean(),
        'notification_follow' => $faker->boolean(),
        'notification_new_spot' => $faker->boolean(),
        'notification_coming_spot' => $faker->boolean(),
        'verified' => true
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

$factory->defineAs(App\Social::class, 'facebook', function (Generator $faker) {
    return [
        'name' => 'facebook',
        'display_name' => 'Facebook'
    ];
});

$factory->defineAs(App\Social::class, 'google', function (Generator $faker) {
    return [
        'name' => 'google',
        'display_name' => 'Google+',
    ];
});

$factory->define(App\Spot::class, function (Generator $faker) use ($timestamps, $dates) {
    $start_date = $faker->dateTimeBetween('-50 days','+50 days');
    $end_date = clone $start_date->modify('+' . mt_rand(1, 5) . ' day');
    $web_sites = range(0, mt_rand(1, 5));
    $web_sites = array_map(function ($value) use ($faker) {
        return $faker->url;
    }, $web_sites);
    $videos = range(0, mt_rand(1, 5));
    $videos = array_map(function ($value) use ($faker) {
        return $faker->url;
    }, $videos);
    return array_merge([
        'cover' => $faker->image(storage_path('app')),
        'title' => $faker->sentence,
        'description' => $faker->sentence,
        'web_sites' => $web_sites,
        'videos' => $videos
    ], $dates(), $timestamps());
});

$factory->define(App\SpotPhoto::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'photo' => $faker->image(storage_path('app'), mt_rand(300, 1920), mt_rand(200, 1250))
    ], $timestamps());
});

$factory->define(App\SpotPoint::class, function (Generator $faker) {
    return [
        'address' => $faker->address,
        'location' => new Point($faker->latitude, $faker->longitude)
    ];
});

$factory->defineAs(App\SpotType::class, 'event', function (Generator $faker) {
    return [
        'name' => 'event',
        'display_name' => 'Event'
    ];
});

$factory->defineAs(App\SpotType::class, 'todo', function (Generator $faker) {
    return [
        'name' => 'todo',
        'display_name' => 'To-Do'
    ];
});

$factory->defineAs(App\SpotType::class, 'food', function (Generator $faker) {
    return [
        'name' => 'food',
        'display_name' => 'Food'
    ];
});

$factory->defineAs(App\SpotType::class, 'shelter', function (Generator $faker) {
    return [
        'name' => 'shelter',
        'display_name' => 'Shelter'
    ];
});

$factory->define(App\SpotTypeCategory::class, function (Generator $faker) {
    $name = $faker->unique()->word;
    return [
        'icon' => $faker->image(storage_path('app'), 70, 70),
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
        'icon' => $faker->image(storage_path('app'), 70, 70),
        'name' => $name,
        'display_name' => ucfirst($name)
    ];
});

$factory->define(App\Album::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'title' => $faker->sentence,
        'is_private' => $faker->boolean(),
        'address' => $faker->address,
        'location' => new Point($faker->latitude, $faker->longitude)
    ], $timestamps());
});

$factory->define(App\AlbumPhoto::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'photo' => $faker->image(storage_path('app'), mt_rand(300, 1920), mt_rand(200, 1250)),
        'address' => $faker->address,
        'location' => new Point($faker->latitude, $faker->longitude),
    ], $timestamps());
});

$factory->define(App\Comment::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'body' => $faker->sentence(16)
    ], $timestamps());
});

$factory->define(App\Area::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'title' => $faker->sentence,
        'description' => $faker->sentence(12),
        'data' => json_encode($faker->latitude),
        'waypoints' => json_encode($faker->latitude),
        'zoom' => mt_rand(1, 32)
    ], $timestamps());
});

$factory->define(App\Blog::class, function (Generator $faker) use ($timestamps) {
    return array_merge([
        'cover' => $faker->image(storage_path('app'), mt_rand(300, 1920), mt_rand(200, 1250)),
        'title' => $faker->sentence,
        'body' => $faker->text(300),
        'slug' => $faker->slug,
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
        'avatar' => $faker->image(storage_path('app'), mt_rand(300, 1920), mt_rand(200, 1250)),
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
