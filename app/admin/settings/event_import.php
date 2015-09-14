<?php

use App\Services\SpotsImportFile;

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
     * @param array $data
     *
     * @return string (on error) / void (otherwise)
     */
    'before_save' => function (&$data) {
    },
    /**
     * The permission option is an authentication check that lets you define a closure that should return true if the
     * current user is allowed to view this settings page. Any "falsey" response will result in a 404.
     *
     * @type closure
     */
    'permission' => function () {
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
            'action' => function (&$data) {
                /**
                 * @var SpotsImportFile $import
                 */
                $import = app(SpotsImportFile::class, [app(), app(Maatwebsite\Excel\Excel::class), $data['document']]);

                app(\Illuminate\Contracts\Bus\Dispatcher::class)
                    ->dispatch(new \App\Jobs\SpotsImport($import, $data, \App\Jobs\SpotsImport::EVENT));

                return true;
            }
        ],
        'get_log' => [
            'title' => 'Show import log',
            'messages' => [
                'active' => 'Getting log file...',
                'success' => 'Log page redirecting',
                'error' => 'There was an error while opening log file',
            ],
            //the settings data is passed to the closure and saved if a truthy response is returned
            'action' => function(&$data)
            {
                return \App\Jobs\SpotsImport::getLog(\App\Jobs\SpotsImport::EVENT);
            }
        ],
        'crear_log' => [
            'title' => 'Clear import log',
            'messages' => [
                'active' => 'Clearing log file...',
                'success' => 'Log successfuly deleted',
                'error' => 'There was an error while deleting log file',
            ],
            //the settings data is passed to the closure and saved if a truthy response is returned
            'action' => function(&$data)
            {
                return \App\Jobs\SpotsImport::removeLog(\App\Jobs\SpotsImport::EVENT);
            }
        ]
    ],
    'storage_path' => storage_path() . '/csvs',
];
