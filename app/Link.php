<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Link
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
    protected $guarded = ['id'];

    public function linkable()
    {
        return $this->morphTo();
    }
}
