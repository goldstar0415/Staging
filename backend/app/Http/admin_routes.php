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
get('spots/refresh-view', 'SpotController@refreshMaterializedView')->name('admin.spots.refresh-view');
put('spots/{id}/spot-points/{spot_point_id}', 'SpotController@updateSpotPoint')->name('admin.spots.update-spot-point');
post('spots/{id}/spot-points', 'SpotController@createSpotPoint')->name('admin.spots.create-spot-point');

post('csv-parser/export', 'CsvParserController@export')->name('admin.csv-parser.export');
post('csv-parser/exportUpload', 'CsvParserController@exportUpload')->name('admin.csv-parser.export-upload');
resource('csv-parser', 'CsvParserController', ['only' => ['index']]);

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
get('settings/ticketmaster-run', 'SettingsController@ticketMasterRun')->name('admin.ticket-master.run');
get('settings/heyevent-run', 'SettingsController@heyeventRun')->name('admin.heyevent.run');
get('settings/heyeventimport-run', 'SettingsController@heyeventImportRun')->name('admin.heyeventimport.run');
