<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/**
 * User resource
 */
Route::get('users/{users}/albums', 'AlbumController@showForUser');
Route::get('users/me', 'UserController@getMe');
Route::get('users/list', 'UserController@getList');
Route::post('users', 'UserController@postIndex');
Route::post('users/login', 'UserController@postLogin');
Route::get('users/logout', 'UserController@getLogout');
Route::post('users/recovery', 'UserController@postRecovery');
Route::post('users/reset', 'UserController@postReset');
Route::get('users/{users}', 'UserController@getIndex');
Route::get('users/confirm/{token}', 'UserController@confirmEmail');
Route::get('users/email-change/{token}', 'UserController@changeEmail');
get('settings/alias', 'SettingsController@checkAlias');
Route::controller('settings', 'SettingsController');

Route::get('account/{social}', 'SocialAuthController@getAccount');
Route::delete('account/{social}', 'SocialAuthController@deleteAccount');

/**
 * User albums
 */
Route::resource('albums', 'AlbumController', ['except' => ['create', 'edit']]);
/**
 * Album photos
 */
Route::resource('photos', 'AlbumPhotoController', ['only' => ['show', 'update', 'destroy']]);
Route::get('photos/{photos}/avatar', 'AlbumPhotoController@setAvatar');
Route::resource('photos.comments', 'AlbumPhotoCommentController', ['only' => ['index', 'store', 'destroy']]);
Route::get('albums/{albums}/photos', 'AlbumPhotoController@photos');
/**
 * Follow
 */
Route::get('follow/{users}', 'FollowController@getFollow');
Route::get('unfollow/{users}', 'FollowController@getUnfollow');
Route::get('followers/{users}', 'FollowController@getFollowers');
Route::get('followings/{users}', 'FollowController@getFollowings');
/**
 * Friends resource
 */
Route::resource('friends', 'FriendController', ['except' => ['create', 'edit']]);
Route::post('friends/{friends}/setavatar', 'FriendController@setAvatar');
/**
 * Spot resource
 */
Route::get('spots/categories', 'SpotController@categories');
Route::get('reviews', 'UserController@reviews');
Route::get('spots/favorites', 'SpotController@favorites');
Route::post('spots/invite', 'SpotController@invite');
Route::post('spots/{spots}/rate', 'SpotController@rate');
Route::post('spots/{spots}/report', 'SpotController@report');
Route::post('spots/{spots}/owner', 'SpotController@ownerRequest');
Route::get('spots/{spots}/favorite', 'SpotController@favorite');
Route::get('spots/{spots}/unfavorite', 'SpotController@unfavorite');
Route::get('spots/{spots}/members', 'SpotController@members');
Route::get('spots/{spots}/preview', 'SpotController@preview');
Route::get('spots/{spots}/export', 'SpotController@export');
Route::resource('spots', 'SpotController', ['except' => ['create', 'edit']]);
Route::resource('spots.comments', 'SpotCommentController', ['except' => ['create', 'edit']]);
Route::resource('spots.photos', 'SpotPhotoController', ['only' => 'store']);
Route::resource(
    'spots.photos.comments',
    'SpotPhotoCommentController',
    ['only' => ['index', 'store', 'destroy', 'update']]
);

/**
 * Calendar controls
 */
Route::get('calendar/export', 'CalendarController@export');
Route::post('calendar/{spots}', 'CalendarController@add');
Route::delete('calendar/{spots}', 'CalendarController@remove');
Route::get('calendar/plans', 'CalendarController@getPlans');
/**
 * Plan controls
 */
Route::resource('plans', 'PlanController', ['except' => ['create', 'edit']]);
Route::get('plans/{plans}/export', 'PlanController@export');
Route::post('plans/invite', 'PlanController@invite');
Route::get('activity-categories', 'PlanController@getActivityCategories');
Route::resource('plans.comments', 'PlanCommentController', ['only' => ['index', 'store', 'destroy']]);
/**
 * Chat Controls
 */
Route::post('message', 'ChatController@sendMessage');
Route::get('message/dialogs', 'ChatController@getDialogs');
Route::get('message/list', 'ChatController@getList');
Route::delete('message/dialogs/{id}', 'ChatController@destroyDialog');
Route::delete('message/{message}', 'ChatController@destroy');
Route::get('message/{user_id}/read', 'ChatController@read');
/**
 * Map Controls
 */
Route::get('map/spots', 'MapController@getSpots');
Route::get('map/spots/list', 'MapController@getList');
Route::resource('areas', 'AreaController', ['except' => ['create', 'edit']]);
Route::get('areas/{areas}/preview', 'AreaController@preview');
Route::get('weather', 'MapController@getWeather');
/**
 * Wall Controls
 */
Route::resource('wall', 'WallController', ['except' => ['create', 'edit']]);
Route::get('wall/{wall}/like', 'WallController@like');
Route::get('wall/{wall}/dislike', 'WallController@dislike');
/**
 * Feed Controls
 */
Route::get('feeds', 'FeedController@index');
/**
 * Blog Controls
 */
Route::get('posts/categories', 'BlogController@categories');
Route::get('posts/popular', 'BlogController@popular');
Route::post('posts/request', 'BlogController@bloggerRequest');
Route::post('posts/upload', 'BlogController@upload');
Route::resource('posts', 'BlogController', ['except' => ['create', 'edit']]);
Route::resource('posts.comments', 'BlogCommentController', ['only' => ['index', 'store', 'destroy']]);
Route::get('posts/{posts}/preview', 'BlogController@preview');
//-----------------------------------------------
Route::get('file', 'DownloadController@index');
Route::get('url-parse', 'UrlMetaParserController@getContentFromSite');

Route::group(['prefix' => 'import/logs', 'middleware' => 'admin'], function () {
    get('{type}', 'ShowLogController@show');
});

Route::post('contact-us', 'UserController@contactUs');

get('google-contacts', 'SocialContactsController@google');

get('test', function () {


    return App\User::with('calendarSpots')->first();
});

get('test-update', function () {
    /**
     * @var $spot App\Spot
     */
    $spot = App\Spot::with('tags')->first();
    $spot->tags()->sync([1]);
});