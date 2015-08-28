<?php

return [
    'title' => 'Users',
    'single' => 'user',
    'model' => App\User::class,
    /**
     * The display columns
     */
    'columns' => [
        'id',
        'first_name' => [
            'title' => 'First Name'
        ],
        'last_name' => [
            'title' => 'Last Name'
        ],
        'email' => [
            'title' => 'Email',
        ],
        'birth_date' => [
            'title' => 'Birth Date',
        ]
    ],
    /**
     * The filter set
     */
    'filters' => [
        'id',
        'first_name' => [
            'title' => 'First Name',
        ],
        'last_name' => [
            'title' => 'Last Name',
        ],
        'birth_date' => [
            'title' => 'Birth Date',
            'type' => 'date'
        ],
    ],
    /**
     * The editable fields
     */
    'edit_fields' => [
        'first_name' => [
            'title' => 'First Name',
            'type' => 'text',
        ],
        'last_name' => [
            'title' => 'Last Name',
            'type' => 'text',
        ],
        'birth_date' => [
            'title' => 'Birth Date',
            'type' => 'date',
        ],
        'roles' => [
            'title' => 'Role',
            'type' => 'relationship',
            'name_field' => 'name'
        ],
        'banned_at' => [
            'title' => 'Ban',
            'type' => 'bool',
        ],
        'ban_reason' => [
            'title' => 'Ban Reason',
            'type' => 'text'
        ]
    ],
    'form_width' => 600
];