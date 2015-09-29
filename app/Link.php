<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Link
 * @package App
 *
 * @property string $title
 * @property string $description
 * @property string $url
 * @property string $default_url
 * @property array $images
 */
class Link extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $guarded = ['id'];

    /**
     * Get all of the owning linkable models
     */
    public function linkable()
    {
        return $this->morphTo();
    }
}
