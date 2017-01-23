<?php

get('/', 'UsersController@index');
resource('activitylevel', 'ActivityLevelController', ['except' => 'show']);

get('users/search', 'UsersController@search')->name('admin.users.search');
get('users/bulk-delete', 'UsersController@bulkDelete')->name('admin.users.bulk-delete');
resource('users', 'UsersController', ['except' => ['store', 'create']]);

resource('spots', 'SpotController', ['only' => ['index', 'destroy']]);
get('spots/search', 'SpotController@search')->name('admin.spots.search');
get('spots/filter', 'SpotController@filter')->name('admin.spots.filter');
get('spots/email-savers', 'SpotController@emailSavers')->name('admin.spots.email-savers');
get('spots/email-list', 'SpotController@emailList')->name('admin.spots.email-list');
get('spots/export-filter', 'SpotController@exportFilter')->name('admin.spots.export-filter');
patch('spots/bulk-update', 'SpotController@bulkUpdate')->name('admin.spots.bulk-update');
get('spots/duplicates', 'SpotController@duplicates')->name('admin.spots.duplicates');
resource('spot-categories', 'SpotCategoriesController', ['except' => 'show']);
resource('spot-reports', 'SpotReportController', ['only' => ['index', 'destroy']]);
get('spot-owner', 'SpotOwnerController@index')->name('admin.spot-owner.index');
get('spot-owner/{owner_request}/accept', 'SpotOwnerController@accept')->name('admin.spot-owner.accept');
get('spot-owner/{owner_request}/reject', 'SpotOwnerController@reject')->name('admin.spot-owner.reject');

post('hotels/cleanDb', 'HotelsController@cleanDb')->name('admin.hotels.clean-db');
get('hotels/bulk-delete', 'HotelsController@bulkDestroy')->name('admin.hotels.bulk-delete');
get('hotels/filter', 'HotelsController@filter')->name('admin.hotels.filter');
get('hotels/hotels_parser', 'HotelsController@hotelsCsvParser')->name('admin.hotels.parser');
post('hotels/export', 'HotelsController@export')->name('admin.hotels.export');
post('hotels/exportUpload', 'HotelsController@exportUpload')->name('admin.hotels.export-upload');
get('hotels/{spots}/edit', 'HotelsController@getEdit')->name('admin.hotels.get-edit');
post('hotels/{spots}/edit', 'HotelsController@postEdit')->name('admin.hotels.post-edit');
post('hotels/update-field', 'HotelsController@updateField')->name('admin.hotels.update_field');
resource('hotels', 'HotelsController', ['only' => ['index', 'destroy']]);

post('restaurants/cleanDb', 'RestaurantsController@cleanDb')->name('admin.restaurants.clean-db');
get('restaurants/bulk-delete', 'RestaurantsController@bulkDestroy')->name('admin.restaurants.bulk-delete');
get('restaurants/filter', 'RestaurantsController@filter')->name('admin.restaurants.filter');
get('restaurants/restaurants_parser', 'RestaurantsController@restaurantsCsvParser')->name('admin.restaurants.parser');
post('restaurants/export', 'RestaurantsController@export')->name('admin.restaurants.export');
post('restaurants/exportUpload', 'RestaurantsController@exportUpload')->name('admin.restaurants.export-upload');
get('restaurants/{spots}/edit', 'RestaurantsController@getEdit')->name('admin.restaurants.get-edit');
post('restaurants/{spots}/edit', 'RestaurantsController@postEdit')->name('admin.restaurants.post-edit');
post('restaurants/update-field', 'RestaurantsController@updateField')->name('admin.restaurants.update_field');
resource('restaurants', 'RestaurantsController', ['only' => ['index', 'destroy']]);

post('todo/cleanDb', 'ToDoController@cleanDb')->name('admin.todo.clean-db');
get('todo/bulk-delete', 'ToDoController@bulkDestroy')->name('admin.todo.bulk-delete');
get('todo/filter', 'ToDoController@filter')->name('admin.todo.filter');
get('todo/parser', 'ToDoController@csvParser')->name('admin.todo.parser');
post('todo/export', 'ToDoController@export')->name('admin.todo.export');
post('todo/exportUpload', 'ToDoController@exportUpload')->name('admin.todo.export-upload');
get('todo/{spots}/edit', 'ToDoController@getEdit')->name('admin.todo.get-edit');
post('todo/{spots}/edit', 'ToDoController@postEdit')->name('admin.todo.post-edit');
post('todo/update-field', 'ToDoController@updateField')->name('admin.todo.update_field');
resource('todoes', 'ToDoController', ['only' => ['index', 'destroy']]);

resource('posts', 'BlogController', ['except' => 'show']);
get('posts/search', 'BlogController@search')->name('admin.posts.search');

resource('blog-categories', 'BlogCategoryController', ['except' => 'show']);
resource('activity-categories', 'ActivityCategoriesController', ['except' => 'show']);

get('spot-import', 'SpotImportController@index')->name('admin.spot-import');
post('spot-import', 'SpotImportController@store')->name('admin.spot-import.store');
get('spot-import/log', 'SpotImportController@getLog')->name('admin.spot-import.log.show');
delete('spot-import/log', 'SpotImportController@deleteLog')->name('admin.spot-import.log.delete');

get('spot-import-columns', 'SpotImportController@indexColumns')->name('admin.spot-import-columns');
post('spot-import-columns', 'SpotImportController@storeColumns');

get('blogger-requests', 'BloggerRequestController@index')->name('admin.blogger-requests.index');
get('blogger-requests/accept/{blogger_request}', 'BloggerRequestController@accept')->name('admin.blogger-requests.accept');
get('blogger-requests/reject/{blogger_request}', 'BloggerRequestController@reject')->name('admin.blogger-requests.reject');

get('spot-requests', 'SpotRequestController@index')->name('admin.spot-requests.index');
get('spot-requests/search', 'SpotRequestController@search')->name('admin.spot-requests.search');
get('spot-requests/approve/{spots}', 'SpotRequestController@approve')->name('admin.spot-requests.approve');
get('spot-requests/reject/{spots}', 'SpotRequestController@reject')->name('admin.spot-requests.reject');

get('contact-us', 'ContactUsController@index')->name('admin.contact-us.index');
delete('contact-us/{contact_us}', 'ContactUsController@destroy')->name('admin.contact-us.destroy');
get('contact-us/search', 'ContactUsController@search')->name('admin.contact-us.search');

get('email', 'EmailController@index')->name('admin.email');
post('email', 'EmailController@send')->name('admin.email.send');
get('email/users', 'EmailController@users')->name('admin.email.users');

get('settings', 'SettingsController@index')->name('admin.settings');
put('settings', 'SettingsController@update');
get('settings/parse-run', 'SettingsController@parserRun')->name('admin.parser.run');
get('settings/crawler-run', 'SettingsController@crawlerRun')->name('admin.crawler.run');
get('settings/ticketmaster-run', 'SettingsController@ticketMasterRun')->name('admin.ticket-master.run');
get('settings/heyevent-run', 'SettingsController@heyeventRun')->name('admin.heyevent.run');
get('settings/heyeventimport-run', 'SettingsController@heyeventImportRun')->name('admin.heyeventimport.run');