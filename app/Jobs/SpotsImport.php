<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Services\SpotsImportFile;
use App\Spot;
use App\SpotTypeCategory;
use App\SpotVote;
use App\User;
use Carbon\Carbon;
use File;
use Illuminate\Contracts\Bus\SelfHandling;
use Log;
use Storage;
use Validator;

class SpotsImport extends Job implements SelfHandling
{
    const EVENT = 'event';
    const RECREATION = 'recreation';
    const PITSTOP = 'pitstop';

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
     * @param array $data
     * @param string $type
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
            if ($row->image_links) {
                $row->put('image_links', explode(',', $row->image_links));
            }
            /**
             * @var \Illuminate\Validation\Validator $validator
             */
            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'string|max:5000',
                'website' => 'url',
                'latitude' => 'required|numeric',
                'longtitude' => 'required|numeric',
                'address' => 'required|string|max:255',
                'rating' => 'numeric'
            ];

            if ($this->type === self::EVENT) {
                $rules['start_date'] = 'required|date_format:Y-m-d';
                $rules['end_date'] = 'required|date_format:Y-m-d';
            }

            if ($row->image_links) {
                for ($i = 0; $i < count($row->image_links); ++$i) {
                    $rules['image_links.' . $i] = 'url';
                }
            }

            $validator = Validator::make($row->all(), $rules);

            if (!$validator->fails()) {
                /**
                 * @var User $admin
                 */
                if ($row->image_links) {
                    $row->put('image_links', array_values(array_filter($row->image_links, function ($value) {
                        return !Validator::make(['photo' => $value], ['photo' => 'remote_image'])->fails();
                    })));
                }
                $admin = User::where('email', $this->data['admin'])->first();
                $spot = new Spot;
                $spot->category()->associate(SpotTypeCategory::where('name', $this->data['spot_category'])->first());
                if ($row->image_links[0]) {
                    $spot->cover = $row->image_links[0];
                }
                $spot->title = $row->title;
                if (!empty($row->description)) {
                    $spot->description = $row->description;
                }
                if (!empty($row->website)) {
                    $spot->web_sites = [$row->website];
                }
                if ($this->type === self::EVENT) {
                    $spot->start_date = Carbon::createFromFormat('Y-m-d', $row->start_date);
                    $spot->end_date = Carbon::createFromFormat('Y-m-d', $row->end_date);
                }
                if ($this->type === self::RECREATION or $this->type === self::PITSTOP) {
                    $spot->is_approved = true;
                }

                $admin->spots()->save($spot);
                if ($row->rating) {
                    $vote = new SpotVote(['vote' => $row->rating]);
                    $vote->user()->associate($admin);
                    $spot->votes()->save($vote);
                }
                if ($row->image_links) {
                    foreach ($row->image_links as $photo) {
                        $spot->photos()->create([
                            'photo' => $photo
                        ]);
                    }
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
            } else {
                $this->log($row, $validator);
            }
        });

        File::delete([
            storage_path('csvs/' . $this->type . '_import.json'),
            storage_path('app/' . $this->data['document'])
        ]);

    }

    /**
     * @param $row
     * @param $validator
     */
    protected function log($row, $validator)
    {
        $log_file = $this->type . '-import.log';

        $errors = implode("\n", array_map(function ($value) {
            return '- ' . $value;
        }, $validator->messages()->all()));

        $date = date('Y/m/d H:i:s');
        $text = <<<TEXT
\n\n-----------------------$date------------------------
$row->title\n
Spot '$row->title' hasn't been imported
Errors:
$errors
TEXT;
        if (!File::exists(storage_path('logs/' . $log_file))) {
            File::put(storage_path('logs/' . $log_file), $text);
        } else {
            File::append(storage_path('logs/' . $log_file), $text);
        }
    }

    public static function getLog($type = self::EVENT)
    {
        return redirect('import/logs/' . $type);
    }

    public static function removeLog($type = self::EVENT)
    {
        $path = storage_path('logs/' . $type . '-import.log');
        if (File::exists($path)) {
            return File::delete($path);
        } else {
            return false;
        }
    }
}
