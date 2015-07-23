<?php

namespace App;

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
    use PostgisTrait, StaplerTrait;

    protected $guarded = ['id', 'user_id'];

    protected $dates = ['birth_date'];

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
