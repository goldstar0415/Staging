<?php

return [
    'title' => 'Blogs',
    'single' => 'blogs',
    'model' => App\Blog::class,
    /**
     * The display columns
     */
    'columns' => [
        'id',
        'title' => [
            'title' => 'Title'
        ],
        'user' => [
            'title' => 'User',
            'relationship' => 'user', //this is the name of the Eloquent relationship method!
            'select' => "CONCAT((:table).first_name, ' ', (:table).last_name)",
        ],
        'cover_link' => [
            'title' => 'Cover',
            'output' => '<img src="(:value)" height="100" width="150" />'
        ],
        'body' => [
            'title' => 'Body'
        ],
        'slug' => [
            'title' => 'Slug',
        ],
        'count_views' => [
            'title' => 'Count views',
        ]
    ],
    /**
     * The filter set
     */
    'filters' => [
        'id',
        'slug' => [
            'title' => 'Alias',
        ],
        'title' => [
            'title' => 'Title',
        ]
    ],
    /**
     * The editable fields
     */
    'edit_fields' => [
        'title' => [
            'title' => 'Title',
            'type' => 'text',
        ],
        'body' => [
            'title' => 'body',
            'type' => 'wysiwyg',
        ],
        'slug' => [
            'title' => 'Alias',
            'type' => 'text',
        ],
        'count_views' => [
            'title' => 'Count views',
            'type' => 'number',
        ],
        'cover_put' => [
            'title' => 'Image',
            'type' => 'image',
            'location' => public_path('tmp/'),
            'naming' => 'random'
        ]
    ],
    'form_width' => 600
];
