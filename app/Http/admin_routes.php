<?php

get('/', 'UsersController@index');
resource('activitylevel', 'ActivityLevelController', ['except' => 'show']);
resource('users', 'UsersController', ['except' => ['store', 'create']]);
resource('spot-categories', 'SpotCategoriesController', ['except' => 'show']);
