<?php

namespace App;

use App\Services\Uploader\Download;
use App\Services\Uploader\Upload;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Friend
 * @package App
 *
 * @property int $id
 * @property integer $user_id
 * @property string $first_name
 * @property string $last_name
 * @property \Carbon\Carbon $birth_date
 * @property string $phone
 * @property string $email
 * @property string $address
 * @property Point $location
 * @property string $note
 *
 * Relation properties
 * @property User $user
 */
class Friend extends BaseModel
{
    use PostgisTrait;

    protected $guarded = ['id', 'user_id'];

    protected $dates = ['birth_date'];

    protected $postgisFields = [
        'location' => Point::class,
    ];

    public function setAvatarAttribute(UploadedFile $file)
    {
        /**
         * @var Upload $upload
         */
        $upload = app(Upload::class);
        $upload->make($file, $this, 'avatar')->save();
    }

    public function getAvatarUrlAttribute()
    {
        /**
         * @var Download $download
         */
        $download = app(Download::class);
        $download->link($this, 'avatar');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
