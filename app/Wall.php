<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Wall
 * @package App
 *
 * @property integer $id
 * @property integer $sender_id
 * @property integer $receiver_id
 * @property string $body
 *
 * Relation properties
 * @property SpotType $type
 * @property \Illuminate\Database\Eloquent\Collection $spots
 * @property \Illuminate\Database\Eloquent\Collection $albumPhotos
 * @property \Illuminate\Database\Eloquent\Collection $areas
 */
class Wall extends BaseModel
{
    use SoftDeletes;

    protected $fillable = ['body'];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function spots()
    {
        return $this->belongsToMany(Spot::class);
    }

    public function albumPhotos()
    {
        return $this->belongsToMany(AlbumPhoto::class);
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class);
    }
}
