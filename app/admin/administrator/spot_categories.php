<?php

return [
    'title' => 'Spot Categories',
    'single' => 'spot_type_categories',
    'model' => \App\SpotTypeCategory::class,

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
        'type' => [
            'title' => "Spot Type",
            'type' => 'relationship', //this is the name of the Eloquent relationship method!
            'name_field' => 'display_name',
        ],
        'icon_put' => [
            'title' => 'Image',
            'type' => 'image',
            'location' => public_path('tmp/'),
            'naming' => 'random'
        ]
    ]
];
