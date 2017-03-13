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
    protected $fillable = ['name', 'display_name'];

    public $timestamps = false;

    /**
     * Get blogs for the category
     */
    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }
}
