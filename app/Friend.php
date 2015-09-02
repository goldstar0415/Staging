<?php

namespace App;

use App\Extensions\GeoTrait;
use Carbon\Carbon;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Codesleeve\Stapler\ORM\EloquentTrait as StaplerTrait;

/**
 * Class Friend
 * @package App
 *
 * @property int $id
 * @property integer $user_id
 * @property integer $friend_id
 * @property string $avatar
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
class Friend extends BaseModel implements StaplerableInterface
{
    use PostgisTrait, StaplerTrait, GeoTrait;

    protected $guarded = ['id', 'user_id', 'friend_id'];

    protected $dates = ['birth_date'];

    protected $appends = ['avatar_url', 'default_location'];

    protected $hidden = ['avatar_file_name', 'avatar_content_type', 'avatar_file_size'];

    protected $postgisFields = [
        'location' => Point::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('avatar');
        parent::__construct($attributes);
    }

    public function getAvatarUrlAttribute()
    {
        return $this->getPictureUrls('avatar');
    }

    public function getDefaultLocationAttribute()
    {
        if ($this->user_id) {
            return $this->user->location;
        }

        return null;
    }

    public function setBirthDateAttribute($value)
    {
        if (!$value instanceof Carbon) {
            $this->attributes['birth_date'] = $value ? Carbon::createFromFormat(
                config('app.date_format'),
                $value
            ) : $value;
        } else {
            $this->attributes['birth_date'] = $value;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function friend()
    {
        return $this->belongsTo(User::class, 'friend_id');
    }
}
