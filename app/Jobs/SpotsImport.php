<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Services\SpotsImportFile;
use App\Spot;
use App\SpotTypeCategory;
use App\User;
use File;
use Illuminate\Contracts\Bus\SelfHandling;
use Validator;

class SpotsImport extends Job implements SelfHandling
{
    const EVENT = 1;
    const RECREATION = 2;
    const PITSTOP = 3;

    /**
     * @var SpotsImportFile
     */
    private $importFile;
    /**
     * @var int
     */
    private $type;
    /**
     * @var array
     */
    private $data;

    /**
     * Create a new job instance.
     *
     * @param SpotsImportFile $importFile
     * @param $data
     * @param int $type
     */
    public function __construct(SpotsImportFile $importFile, array $data, $type = self::EVENT)
    {
        $this->importFile = $importFile;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->importFile->each(function ($row) {
            $row->put('image_links', explode(',', $row->image_links));
            /**
             * @var \Illuminate\Validation\Validator $validator
             */
            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'string|max:5000',
                'website' => 'url',
                'latitude' => 'numeric',
                'longtitude' => 'numeric',
                'address' => 'string|max:255',
                'rating' => 'numeric'
            ];

            if ($this->type === self::EVENT) {
                $rules['start_date'] = 'date_format:Y-m-d H:i:s';
                $rules['end_date'] = 'date_format:Y-m-d H:i:s';
            }

            for ($i = 0; $i < count($row->image_links); ++$i) {
                $rules['image_links.' . $i] = 'url';
            }

            $validator = Validator::make($row->all(), $rules);

            if (!$validator->fails()) {
                /**
                 * @var User $admin
                 */
                $row->put('image_links', array_values(array_filter($row->image_links, function ($value) {
                    return !Validator::make(['photo' => $value], ['photo' => 'remote_image'])->fails();
                })));
                $admin = User::where('email', $this->data['admin'])->first();
                $spot = new Spot;
                $spot->category()->associate(SpotTypeCategory::where('name', $this->data['spot_category'])->first());
                $spot->cover = $row->image_links[0];
                $spot->title = $row->title;
                $spot->description = $row->description;
                $spot->web_sites = [$row->website];

                if ($this->type === self::EVENT) {
                    $spot->start_date = $row->start_date;
                    $spot->end_date = $row->end_date;
                }

                if ($this->type === self::RECREATION or $this->type === self::PITSTOP) {
                    $spot->is_approved = true;
                }

                $admin->spots()->save($spot);
                foreach ($row->image_links as $photo) {
                    $spot->photos()->create([
                        'photo' => $photo
                    ]);
                }
                $spot->locations = [
                    [
                        'location' => [
                            'lat' => $row->latitude,
                            'lng' => $row->longtitude
                        ],
                        'address' => $row->address
                    ]
                ];
            }
        });

        $json_name = '';

        switch ($this->type) {
            case self::PITSTOP:
                $json_name = 'pitstop';
                break;
            case self::EVENT:
                $json_name = 'event';
                break;
            case self::RECREATION:
                $json_name = 'recreation';
                break;
        }

        File::delete([
            storage_path('csvs/' . $json_name . '_import.json'),
            storage_path('app/' . $this->data['document'])
        ]);

    }
}
