<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BlogCategory
 * @package App
 * 
 * @property integer $id
 * @property string $name
 */
class BlogCategory extends Model
{
    protected $fillable = ['name'];

    public $timestamps = false;

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }
}
