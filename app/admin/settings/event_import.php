<?php

use App\Services\SpotsImport;
use App\Spot;
use App\SpotTypeCategory;
use App\User;

return [
    /**
     * Settings page title
     *
     * @type string
     */
    'title' => 'Import Event',
    /**
     * The edit fields array
     *
     * @type array
     */
    'edit_fields' => [
        'spot_category' => [
            'title' => 'Event category',
            'type' => 'enum',
            'options' => App\SpotType::where('name', 'event')->first()->categories->pluck('display_name', 'name')
                ->toArray()
        ],
        'admin' => [
            'title' => 'Attached admin',
            'type' => 'enum',
            'options' => App\Role::take('admin')->users->pluck('first_name', 'email')->toArray()
        ],
        'document' => [
            'title' => 'CSV file',
            'type' => 'file',
            'location' => storage_path() . '/app/',
            'naming' => 'random',
            'length' => 20,
            'size_limit' => 5
        ]
    ],
    /**
     * The validation rules for the form, based on the Laravel validation class
     *
     * @type array
     */
    'rules' => [
        'spot_category' => 'required',
    ],
    /**
     * This is run prior to saving the JSON form data
     *
     * @type function
     * @param array		$data
     *
     * @return string (on error) / void (otherwise)
     */
    'before_save' => function(&$data)
    {
    },
    /**
     * The permission option is an authentication check that lets you define a closure that should return true if the current user
     * is allowed to view this settings page. Any "falsey" response will result in a 404.
     *
     * @type closure
     */
    'permission'=> function()
    {
        return true;
    },
    /**
     * This is where you can define the settings page's custom actions
     */
    'actions' => [
        //Ordering an item up
        'import_csv' => [
            'title' => 'Import CSV',
            'messages' => [
                'active' => 'Importing csv...',
                'success' => 'CSV successfuly imported',
                'error' => 'There was an error while importing csv',
            ],
            //the settings data is passed to the closure and saved if a truthy response is returned
            'action' => function(&$data)
            {
                /**
                 * @var SpotsImport $import
                 */
                $import = app(SpotsImport::class, [app(), app(Maatwebsite\Excel\Excel::class), $data['document']]);

                $import->each(function($row) use ($data, $import) {
                    $admin = User::where('email', $data['admin'])->first();
                    $photos = explode(',', $row->image_links);
                    $spot = new Spot;
                    $spot->category()->associate(SpotTypeCategory::where('name', $data['spot_category'])->first());
                    $spot->cover = $photos[0];
                    $spot->title = $row->title;
                    $spot->description = $row->description;
                    $spot->start_date = $row->start_date;
                    $spot->end_date = $row->end_date;
                    $admin->spots()->save($spot);
                    foreach ($photos as $photo) {
                        $spot->photos()->create([
                            'photo' => $photo
                        ]);
                    }
                });

                File::delete([
                    storage_path('app/csvs/event_import.json'),
                    storage_path('app/' . $data['document'])
                ]);

                return true;
            }
        ],
    ],
    'storage_path' => storage_path() . '/app/csvs',
];