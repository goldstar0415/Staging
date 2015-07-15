<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class StaticPage
 * @package App
 * 
 * @property integer $id
 * @property string $title
 * @property string $body
 */
class StaticPage extends Model
{
   protected $guarded = ['id'];
}
