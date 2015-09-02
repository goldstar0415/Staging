<?php

return [
    'title' => 'Activity Level',
    'single' => 'activity_level',
    'model' => \App\ActivityLevel::class,

    'columns' => [
    'id' => [
        'title' => 'ID'
    ],
    'name' => [
        'name' => 'Title'
    ],
    'favorites_count' => [
        'title' => 'Quantity'
    ]
],

    'edit_fields' => [
    'name' => [
        'title' => 'Title',
        'type' => 'text'
    ],
    'favorites_count' => [
        'title' => 'Quantity',
        'type' => 'number'
    ]
]
];