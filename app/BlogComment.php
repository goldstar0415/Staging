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
}
