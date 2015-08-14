<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

/**
 * Class BaseModel
 * @package App
 *
 * @method static \Illuminate\Database\Eloquent\Builder random(int $count = 1)
 */
abstract class BaseModel extends Model
{

    protected $date_format = 'Y-m-d';

    public function scopeRandom($query, $count = 1)
    {
        return $query->orderBy(DB::raw(config('database.connections.' . config('database.default') . '.rand_func')))
            ->take($count);
    }

    /**
     * @inheritdoc
     */
    public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null)
    {
        $relation_name = class_basename($related);
        if ($foreignKey === null) {
            $foreignKey = snake_case($relation_name) . '_id';
        }
        return parent::belongsTo($related, $foreignKey, $otherKey, $relation_name);
    }

    protected function getPictureUrls($picture)
    {
        $urls['original'] = $this->$picture->url();
        $urls['medium'] = $this->$picture->url('medium');
        $urls['thumb'] = $this->$picture->url('thumb');

        return $urls;
    }
}
