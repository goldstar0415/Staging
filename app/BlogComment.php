<?php

namespace App;

/**
 * Class BlogComment
 * @package App
 * 
 * @property integer $id
 * @property integer $user_id
 * @property integer $blog_id
 * @property string $body
 */
class BlogComment extends BaseModel
{
    protected $fillable = ['body'];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
