<?php

return [
    'title' => 'Bloggers requests',
    'single' => 'bloggers_requests',
    'model' => \App\BloggerRequest::class,

    'columns' => [
        'user_name' => [
            'title' => "User Name",
            'relationship' => 'user', //this is the name of the Eloquent relationship method!
            'select' => "CONCAT((:table).first_name, ' ', (:table).last_name)",
        ],
        'user_avatar' => [
            'title' => 'Avatar',
            'output' => '<img src="(:value)" height="100" width="100" />'
        ],
        'status' => [
            'title' => 'Status'
        ],
        'text' => [
            'title' => 'Text'
        ]
    ],

    'edit_fields' => [
        'status' => [
            'title' => 'Status',
            'type' => 'enum',
            'options' => ['requested', 'rejected', 'accepted']],
    ]
];
