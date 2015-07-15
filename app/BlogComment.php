<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BlogComment
 * @package App
 * 
 * @property integer $id
 * @property integer $user_id
 * @property integer $blog_id
 * @property string $body
 */
class BlogComment extends Model
{
    protected $fillable = ['body'];
}
