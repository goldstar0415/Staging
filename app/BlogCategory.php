<?php

namespace App;

/**
 * Model BlogCategory
 * @package App
 *
 * @property integer $id
 * @property string $name
 *
 * Relation properties
 * @property \Illuminate\Database\Eloquent\Collection $blogs
 */
class BlogCategory extends BaseModel
{
    protected $fillable = ['name'];

    public $timestamps = false;

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }
}
