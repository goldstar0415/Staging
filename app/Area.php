<?php

namespace App;

use App\Services\SocialSharing;

/**
 * Model Area
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property string $description
 * @property string $data
 * @property string $b_box
 * @property string $waypoints
 * @property integer $zoom
 *
 * Relation properties
 * @property User $user
 * @property \Illuminate\Database\Eloquent\Collection $walls
 */
class Area extends BaseModel
{
    protected $fillable = ['title', 'description', 'data', 'waypoints', 'zoom'];

    protected $appends = ['share_links'];

    protected $casts = [
        'data' => 'array',
        'waypoints' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function walls()
    {
        return $this->belongsToMany(Wall::class);
    }

    public function getShareLinksAttribute()
    {
        $url = url('areas', [$this->id, 'preview']);

        return [
            'facebook' => SocialSharing::facebook($url),
            'twitter' => SocialSharing::twitter($url),
            'google' => SocialSharing::google($url)
        ];
    }
}
