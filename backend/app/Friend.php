<?php

namespace App;

use App\Extensions\GeoTrait;
use Carbon\Carbon;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use App\Extensions\Stapler\EloquentTrait as StaplerTrait;

/**
 * Model Friend
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
 * @property User $friend
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
        $this->hasAttachedFile('avatar', [
            'styles' => [
                'thumb' => [
                    'dimensions' => '100x100#',
                    'convert_options' => ['quality' => 100]
                ],
                'medium' => '180x180#'
            ]
        ]);
        parent::__construct($attributes);
    }

    /**
     * Get array of avatar urls
     *
     * @return array
     */
    public function getAvatarUrlAttribute()
    {
        return $this->getPictureUrls('avatar');
    }

    /**
     * Get the friend's default location
     *
     * @return null|array
     */
    public function getDefaultLocationAttribute()
    {
        if ($this->friend_id !== null) {
            $user = $this->friend;
            $default_location = $user->location;
            $address = $user->address;

            if ($default_location) {
                $location['lat'] = $default_location->getLat();
                $location['lng'] = $default_location->getLng();

                if ($address) {
                    $location['address'] = $address;
                }

                return $location;
            }
        }

        return null;
    }

    /**
     * Set the friend's birth date
     *
     * @param \Carbon\Carbon|string $value
     */
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

    /**
     * Get the user that owns the friend
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that belongs to the friend
     */
    public function friend()
    {
        return $this->belongsTo(User::class, 'friend_id');
    }
}
