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
Route::controller('users', 'UserController');
Route::controller('settings', 'SettingsController');

Route::resource('albums', 'AlbumController', ['except' => ['create', 'edit']]);

Route::resource('photos', 'AlbumPhotoController', ['only' => ['show', 'update', 'destroy']]);
Route::get('photos/{photos}/avatar', 'AlbumPhotoController@setAvatar');
Route::resource('photos.comments', 'AlbumPhotoCommentController', ['only' => ['store', 'destroy']]);
//-----------------------------------------------
Route::get('file', 'DownloadController@index');
