<?php

return [
    'title' => 'Contact Us',
    'single' => 'contact_us',
    'model' => \App\ContactUs::class,

    'columns' => [
        'username' => [
            'name' => 'Name'
        ],
        'email' => [
            'title' => 'E-mail',
        ],
        'message' => [
            'title' => 'Message'
        ]
    ],

    'edit_fields' => [
        'username' => [
            'title' => 'Name',
            'type' => 'text'
        ],
        'email' => [
            'title' => 'E-mail',
            'type' => 'text'
        ]
    ]
];
