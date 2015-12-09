<?php

namespace App\Jobs;

use App\GeneratedUser;
use App\Jobs\Job;
use App\Mailers\AppMailer;
use App\Services\SpotsImportFile;
use App\Spot;
use App\SpotTypeCategory;
use App\SpotVote;
use App\User;
use Carbon\Carbon;
use Codesleeve\Stapler\AttachmentConfig;
use Codesleeve\Stapler\Stapler;
use File;
use Illuminate\Contracts\Bus\SelfHandling;
use Log;
use Storage;
use Validator;

/**
 * Class SpotsImport
 * Job for import spot from csv file
 * @package App\Jobs
 */
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
     * @var \App\Mailers\AppMailer
     */
    private $mailer;

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
        $this->mailer = app(AppMailer::class);
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
                'longitude' => 'required|numeric',
                'address' => 'required|string|max:255',
                'rating' => 'numeric'
            ];

            if (isset($row->email)) {
                $rules['email'] = 'required|email';
            }

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
                if ($row->image_links) {
                    $row->put('image_links', array_values(array_filter($row->image_links, function ($value) {
                        return !Validator::make(['photo' => $value], ['photo' => 'remote_image'])->fails();
                    })));
                }
                $spot = new Spot;
                $spot->category()->associate($this->data['spot_category']);
                if (isset($row->image_links[0])) {
                    $options = [
                        'styles' => [
                            'thumb' => [
                                'dimensions' => '70x70#',
                                'convert_options' => ['quality' => 100]
                            ],
                            'medium' => '160x160',
                            'original' => '633x242#'
                        ]
                    ];
                    $config = Stapler::getConfigInstance();
                    $defaultOptions = $config->get('stapler');
                    $options = array_merge($defaultOptions, (array) $options);
                    $storage = $options['storage'];
                    $options = array_replace_recursive($config->get($storage), $options);
                    $options['styles'] = array_merge((array) $options['styles']);
                    $spot->cover->setConfig(new AttachmentConfig('cover', $options));
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
                    $spot->start_date = Carbon::createFromDate(...explode('-', $row->start_date));
                    $spot->end_date = Carbon::createFromDate(...explode('-', $row->end_date));
                }
                $spot->is_approved = true;
                $owner = null;
                if (isset($row->email)) {
                    $owner = $this->generateUser($row->title, $row->email);
                }
                if (!is_null($owner)) {
                    $owner->spots()->save($spot);
                } else {
                    $spot->save();
                }
                if ($row->rating) {
                    $vote = new SpotVote(['vote' => $row->rating]);
                    $vote->user()->associate($this->data['admin']);
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
            $this->data['document']
        ]);

        return true;
    }

    protected function generateUser($title, $email)
    {
        if ($exist_user = User::whereEmail($email)->first()) {
            return $exist_user;
        }
        $password = str_random(12);
        $user = User::create([
            'first_name' => strlen($title) > 64 ? substr($title, 0, 64) : $title,
            'email' => $email,
            'password' => bcrypt($password)
        ]);

        $generated_user = new GeneratedUser(['password' => $password]);
        $generated_user->user()->associate($user);
        $generated_user->save();

        $this->mailer->notifyGeneratedUser($user, $password);

        return $user;
    }

    /**
     * Log non imported spot
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

    /**
     * Get log file
     *
     * @param string $type
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public static function getLog($type = self::EVENT)
    {
        return redirect('import/logs/' . $type);
    }

    /**
     * Remove log file
     *
     * @param string $type
     * @return bool
     */
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
