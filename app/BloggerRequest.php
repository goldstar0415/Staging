<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BloggerRequest
 * @package App
 * 
 * @property integer $id
 * @property integer $user_id
 * @property string $status
 * @property string $text
 */
class BloggerRequest extends Model
{
    protected $fillable = ['status', 'text'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
