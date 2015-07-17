<?php

namespace App;

use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

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
    protected $guarded = ['id', 'user_id'];

    protected $dates = ['birth_date'];

    protected $postgisFields = [
        'location' => Point::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
