<?php

get('/', 'UsersController@index');
resource('activitylevel', 'ActivityLevelController', ['except' => 'show']);
resource('users', 'UsersController', ['except' => ['store', 'create']]);
resource('spot-categories', 'SpotCategoriesController', ['except' => 'show']);
resource('posts', 'BlogController');
resource('blog-categories', 'BlogCategoryController', ['except' => 'show']);
resource('activity-categories', 'ActivityCategoriesController', ['except' => 'show']);

get('spot-import', 'SpotImportController@index')->name('admin.spot-import');
post('spot-import', 'SpotImportController@store')->name('admin.spot-import.store');
get('spot-import/log', 'SpotImportController@getLog')->name('admin.spot-import.log.show');
delete('spot-import/log', 'SpotImportController@deleteLog')->name('admin.spot-import.log.delete');

get('blogger-requests', 'BloggerRequestController@index')->name('admin.blogger-requests.index');
get('blogger-requests/accept/{blogger_request}', 'BloggerRequestController@accept')->name('admin.blogger-requests.accept');
get('blogger-requests/reject/{blogger_request}', 'BloggerRequestController@reject')->name('admin.blogger-requests.reject');