<?php

return [
    'title' => 'Bloggers requests',
    'single' => 'bloggers_requests',
    'model' => \App\BloggerRequest::class,

    'columns' => [
        'id' => [
            'title' => 'ID'
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