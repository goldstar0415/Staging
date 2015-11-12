<?php

get('/', 'UsersController@index');
resource('activitylevel', 'ActivityLevelController', ['except' => 'show']);
resource('users', 'UsersController', ['except' => ['store', 'create']]);
resource('spot-categories', 'SpotCategoriesController', ['except' => 'show']);
resource('posts', 'BlogController');
resource('blog-categories', 'BlogCategoryController', ['except' => 'show']);
get('spot-import', 'SpotImportController@index')->name('admin.spot-import');
post('spot-import', 'SpotImportController@store')->name('admin.spot-import.store');
get('spot-import/log', 'SpotImportController@getLog')->name('admin.spot-import.log.show');
delete('spot-import/log', 'SpotImportController@deleteLog')->name('admin.spot-import.log.delete');
