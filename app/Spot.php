<?php

namespace App;

use App\Services\Uploader\Download;
use App\Services\Uploader\Upload;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Spot
 * @package App
 * 
 * @property integer $id
 * @property integer $user_id
 * @property integer $spot_type_category_id
 * @property string $title
 * @property string $description
 * @property string $web_site
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 *
 * Relation properties
 * @property User $user
 * @property SpotTypeCategory $category
 * @property \Illuminate\Database\Eloquent\Collection $walls
 * @property \Illuminate\Database\Eloquent\Collection $votes
 * @property \Illuminate\Database\Eloquent\Collection $reviews
 * @property \Illuminate\Database\Eloquent\Collection $favorites
 * @property \Illuminate\Database\Eloquent\Collection $tags
 * @property \Illuminate\Database\Eloquent\Collection $plans
 * @property \Illuminate\Database\Eloquent\Collection $points
 */
class Spot extends BaseModel
{
    protected $guarder = ['id', 'user_id', 'spot_type_category_id'];

    protected $appends = ['rating', 'cover_url'];

    protected $dates = ['start_date', 'end_date'];

    public function getRatingAttribute()
    {
        return (float)$this->votes()->avg('vote');
    }

    public $files_dir = 'user_rel/name/id';


    public function setCoverAttribute(UploadedFile $file)
    {
        /**
         * @var Upload $upload
         */
        $upload = app(Upload::class);
        $upload->make($file, $this, 'cover')->save();
    }

    public function getCoverUrlAttribute()
    {
        /**
         * @var Download $upload
         */
        $download = app(Download::class);
        return $download->link($this, 'cover');
    }

    public function setPhotoAttribute(UploadedFile $file)
    {
        /**
         * @var Upload $upload
         */
        $upload = app(Upload::class);
        $upload->make($file, $this)->randomName()->save();
    }

    public function getPhotoUrlAttribute()
    {
        /**
         * @var Download $download
         */
        $download = app(Download::class);
        return $download->randomName()->link($this);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function walls()
    {
        return $this->belongsToMany(Wall::class);
    }

    public function votes()
    {
        return $this->hasMany(SpotVote::class);
    }

    public function reviews()
    {
        return $this->hasMany(SpotReview::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function category()
    {
        return $this->belongsTo(SpotTypeCategory::class);
    }

    public function points()
    {
        return $this->hasMany(SpotPoint::class);
    }

    public function plans()
    {
        return $this->belongsToMany(Plan::class);
    }
}
