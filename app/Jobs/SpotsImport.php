<?php

namespace App\Jobs;

use App\GeneratedUser;
use App\Jobs\Job;
use App\Mailers\AppMailer;
use App\Services\GoogleAddress;
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
use Vinkla\Instagram\InstagramManager;

abstract class SpotsImport extends Job implements SelfHandling
{
    const EVENT = 'event';
    const TODO = 'todo';
    const FOOD = 'food';
    const SHELTER = 'shelter';

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

    protected $date_format = 'm/d/Y';

    /**
     * Create a new job instance.
     *
     * @param array $data
     * @param string $type
     */
    public function __construct(array $data, $type = self::EVENT)
    {
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    abstract public function getSpots();

    protected function import($imported_spot)
    {
        if ($imported_spot->image_links) {
            $imported_spot->put('image_links', explode(',', $imported_spot->image_links));
        }

        if (isset($this->data['get_address']) and
            $this->data['get_address'] and
            !$imported_spot->address and
            $imported_spot->latitude and
            $imported_spot->longitude) {
            /**
             * @var GoogleAddress $google_address
             */
            $google_address = app(GoogleAddress::class);
            $imported_spot->put('address', $google_address->get($imported_spot->latitude, $imported_spot->longitude));
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

        if (isset($imported_spot->email)) {
            $rules['email'] = 'required|email';
        }

        if ($this->type === self::EVENT) {
            $rules['start_date'] = 'required|date_format:' . $this->date_format;
            $rules['end_date'] = 'required|date_format:' . $this->date_format;
        }

        if ($imported_spot->image_links) {
            for ($i = 0; $i < count($imported_spot->image_links); ++$i) {
                $rules['image_links.' . $i] = 'url';
            }
        }

        $validator = Validator::make($imported_spot->all(), $rules);

        if (!$validator->fails()) {
            if ($imported_spot->image_links) {
                $imported_spot->put('image_links', array_values(array_filter($imported_spot->image_links, function ($value) {
                    return !Validator::make(['photo' => $value], ['photo' => 'remote_image'])->fails();
                })));
            }
            $spot = new Spot;
            $spot->category()->associate($this->data['spot_category']);
            if (!$imported_spot->image_links and
                isset($this->data['instagram_photos']) and
                $this->data['instagram_photos']) {
                $instagram = app(InstagramManager::class);
                $imported_spot->put('image_links', $this->instagramPhotos($instagram->searchMedia(
                    $imported_spot->latitude,
                    $imported_spot->longitude
                )->data));
            }
            if (isset($imported_spot->image_links[0])) {
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
                $spot->cover = $imported_spot->image_links[0];
            }
            $spot->title = $imported_spot->title;
            if (!empty($imported_spot->description)) {
                $spot->description = $imported_spot->description;
            }
            if (!empty($imported_spot->website)) {
                $spot->web_sites = [$imported_spot->website];
            }
            if ($this->type === self::EVENT) {
                $spot->start_date = Carbon::createFromFormat($this->date_format, $imported_spot->start_date);
                $spot->end_date = Carbon::createFromFormat($this->date_format, $imported_spot->end_date);
            }
            $spot->is_approved = true;
            $owner = null;
            if (isset($imported_spot->email)) {
                $owner = $this->generateUser($imported_spot->title, $imported_spot->email);
            }
            if (!is_null($owner)) {
                $owner->spots()->save($spot);
            } else {
                $spot->save();
            }
            if ($imported_spot->rating) {
                $vote = new SpotVote(['vote' => $imported_spot->rating]);
                $vote->user()->associate($this->data['admin']);
                $spot->votes()->save($vote);
            }
            if ($imported_spot->image_links) {
                foreach ($imported_spot->image_links as $photo) {
                    $spot->photos()->create([
                        'photo' => $photo
                    ]);
                }
            }

            $spot->locations = [
                [
                    'location' => [
                        'lat' => $imported_spot->latitude,
                        'lng' => $imported_spot->longitude
                    ],
                    'address' => $imported_spot->address
                ]
            ];

            return true;
        }
        $this->log($imported_spot, $validator);

        return false;
    }

    /**
     * Execute the job.
     * @param AppMailer $mailer
     * @return bool
     */
    public function handle(AppMailer $mailer)
    {
        $this->mailer = $mailer;

        foreach ($this->getSpots() as $spot) {
            $this->import($spot);
        }

        if (isset($this->data['document'])) {
            File::delete($this->data['document']);
        }

        return true;
    }

    protected function instagramPhotos($data)
    {
        $data = collect($data);

        return $data->reject(function ($value) {
            return $value->type === 'video';
        })->sortBy(function ($media) {
            return $media->likes->count;
        })->reverse()->take(5)->map(function ($value) {
            return $value->images->standard_resolution->url;
        })->toArray();
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
