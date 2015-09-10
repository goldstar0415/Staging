<?php

return [
    'title' => 'Activity Categories',
    'single' => 'activity_categories',
    'model' => \App\ActivityCategory::class,

    'columns' => [
        'id' => [
            'title' => 'ID'
        ],
        'display_name' => [
            'title' => 'Display name'
        ]
    ],

    'edit_fields' => [
        'name' => [
            'title' => 'Slug name',
            'type' => 'text'
        ],
        'display_name' => [
            'title' => 'Display name',
            'type' => 'text'
        ],
        'icon_put' => [
            'title' => 'Image',
            'type' => 'image',
            'location' => public_path('tmp/'),
            'naming' => 'random'
        ]
    ]
];
