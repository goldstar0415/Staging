<?php

namespace App;

/**
 * Class BloggerRequest
 * @package App
 * 
 * @property integer $id
 * @property integer $user_id
 * @property string $status
 * @property string $text
 *
 * Relation properties
 * @property User $user
 */
class BloggerRequest extends BaseModel
{
    protected $fillable = ['status', 'text'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
