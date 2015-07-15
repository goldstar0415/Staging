<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Tag
 * @package App
 *
 * @property int $id
 * @property string $name
 */
class Tag extends Model
{
    protected $fillable = ['name'];

    public $timestamps = false;

    public function spots()
    {
        return $this->belongsToMany(Spot::class);
    }
}
