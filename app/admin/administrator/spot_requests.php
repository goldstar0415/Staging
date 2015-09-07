<?php

return [
    'title' => 'Spot requests',
    'single' => 'spot_requests',
    'model' => App\Spot::class,
    /**
     * The display columns
     */
    'columns' => [
        'id',
        'type' => [
            'title' => 'Type',
            'relationship' => 'category.type',
            'select' => '(:table).display_name',
        ],
        'title' => [
            'title' => 'Title'
        ],
        'description' => [
            'title' => 'Description'
        ]
    ],

    'query_filter' => function ($query) {
        /**
         * @var \Illuminate\Database\Query\Builder $query
         */
        $query->join('spot_type_categories', 'spots.spot_type_category_id', '=', 'spot_type_categories.id')
            ->whereIn('spot_type_categories.spot_type_id', function ($query) {
                $query->select('id')->from('spot_types')
                    ->where('spot_types.name', '=', DB::raw("'recreation'"))
                    ->orWhere('spot_types.name', '=', DB::raw("'pitstop'"));
            })->where('is_approved', '=', false);
    },
    /**
     * The filter set
     */
    'filters' => [
        'id',
        'title' => [
            'title' => 'Title',
        ],
        'description' => [
            'title' => 'Description',
        ],
    ],
    /**
     * The editable fields
     */
    'edit_fields' => [
        'is_approved' => [
            'title' => 'Approved',
            'type' => 'bool'
        ],
    ],
    'form_width' => 600
];