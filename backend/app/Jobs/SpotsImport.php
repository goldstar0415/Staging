<?php

namespace App\Jobs;

use App\GeneratedUser;
use App\Jobs\Job;
use App\Mailers\AppMailer;
use App\Services\GoogleAddress;
use App\Spot;
use App\User;
use Carbon\Carbon;
use File;
use Illuminate\Contracts\Bus\SelfHandling;
use Validator;
use Vinkla\Instagram\InstagramManager;
use Cache;
use DB;

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
    protected $data;

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
        
        if (!empty($imported_spot->website)) 
        {
                $imported_spot->put('website', trim($imported_spot->website));
        }
        if (isset($this->data['get_address']) and
            $this->data['get_address'] and
            !$imported_spot->full_address and
            $imported_spot->latitude and
            $imported_spot->longitude) 
        {
            /**
             * @var GoogleAddress $google_address
             */
            $google_address = app(GoogleAddress::class);
            $imported_spot->put('full_address', $google_address->get($imported_spot->latitude, $imported_spot->longitude));
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
            'full_address' => 'required|string|max:255',
        ];

        if (!empty($imported_spot->e_mail)) 
        {
            $rules['e_mail'] = 'required|email';
        }

        if ($this->type === self::EVENT) 
        {
            $rules['start_date'] = 'required|date_format:' . $this->date_format;
            $rules['end_date'] = 'required|date_format:' . $this->date_format;
        }

        if (!empty($imported_spot->picture)) 
        {
            $rules['picture']= 'sometimes|url';
        }
        $validator = Validator::make($imported_spot->all(), $rules);
        if (!$validator->fails())
        {
            if(!empty($imported_spot->picture))
            {
                $imported_spot->put('picture', [trim($imported_spot->picture)]);
            }
            $spot = new Spot;
            $spot->category()->associate($this->data['spot_category']);
            if (empty($imported_spot->picture) and
                !empty($this->data['instagram_photos'])) 
            {
                $instagram = app(InstagramManager::class);
                $imported_spot->put('picture', $this->instagramPhotos($instagram->searchMedia(
                    $imported_spot->latitude,
                    $imported_spot->longitude
                )->data));
            }
            $spot->title = $imported_spot->title;
            if (!empty($imported_spot->description)) 
            {
                $spot->description = $imported_spot->description;
            }
            if (!empty($imported_spot->website))
            {
                $spot->web_sites = [$imported_spot->website];
            }
            if ($this->type === self::EVENT) 
            {
                $spot->start_date = Carbon::createFromFormat($this->date_format, $imported_spot->start_date);
                $spot->end_date = Carbon::createFromFormat($this->date_format, $imported_spot->end_date);
            }
            if (!empty($imported_spot->total_reviews))
            {
                $spot->total_reviews = $imported_spot->total_reviews;
            }
            if (!empty($imported_spot->avg_rating))
            {
                $spot->avg_rating = $imported_spot->avg_rating;
            }
            $spot->is_approved = true;
            $spot->is_private = false;
            $owner = null;
            if ( !empty($imported_spot->e_mail) ) 
            {
                $owner = $this->generateUser($imported_spot->title, $imported_spot->e_mail);
            }
            if (!is_null($owner)) 
            {
                $owner->spots()->save($spot);
            }
            else 
            {
                $spot->save();
            }
            if ($imported_spot->picture) 
            {
                $needCover = true;
                foreach ($imported_spot->picture as $photo) {
                    $cover = $needCover?1:0;
                    if($needCover)
                    {
                        $needCover = false;
                    }
                    $spot->remotePhotos()->create([
                        'image_type' => $cover,
                        'url' => $photo
                    ]);
                }
            }
            if ( !empty($imported_spot->tags)) 
            {
                $tags = explode(';', $imported_spot->tags);
                $tags = array_map('trim', $tags);
                $idsArr = [];
                $tagsRes = [];
                $existingTags = [];
                $tagsCollection = DB::table('tags')->whereIn('name', $tags)->get();
                foreach($tagsCollection as $tagObj)
                {
                    $idsArr[] = $tagObj->id;
                    $existingTags[] = $tagObj->name;
                }
                $tags = array_diff($tags, $existingTags);
                foreach($tags as $tag)
                {
                    $idsArr[] = DB::table('tags')->insertGetId(['name' => $tag]);
                }
                foreach($idsArr as $id)
                {
                    $tagsRes[] = ['spot_id' => $spot->id, 'tag_id' => $id];
                }
                DB::table('spot_tag')->insert($tagsRes);
            }

            $spot->locations = [
                [
                    'location' => [
                        'lat' => $imported_spot->latitude,
                        'lng' => $imported_spot->longitude
                    ],
                    'address' => $imported_spot->full_address
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
        $this->mailer   = $mailer;
        $jobId          = $this->job->getJobId();
        $cacheKey       = "spot-import-{$jobId}-".env('QUEUE_WORK_NAME', 'default');
        if (Cache::has($cacheKey)) 
        {
            return true;
        }
        else 
        {
            Cache::put($cacheKey, $jobId, 10);
        }
        foreach ($this->getSpots() as $spot) 
        {
            $this->import($spot);
        }
        if (isset($this->data['document'])) 
        {
            File::delete($this->data['document']);
        }
        Cache::forget($cacheKey);
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
