<?php

namespace App;

use App\Extensions\Attachments;
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
 * @property User $sender
 * @property User $receiver
 * @property \Illuminate\Database\Eloquent\Collection $spots
 * @property \Illuminate\Database\Eloquent\Collection $albumPhotos
 * @property \Illuminate\Database\Eloquent\Collection $areas
 */
class Wall extends BaseModel
{
    use SoftDeletes, Attachments;

    protected $fillable = ['body'];

    protected $dates = ['deleted_at'];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->addAttachments();
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
