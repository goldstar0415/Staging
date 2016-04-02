<?php

namespace App;

use App\Extensions\Cache\Cacheable;
use Illuminate\Database\Eloquent\Model;
use DB;

/**
 * Model BaseModel
 * @package App
 *
 * @method static \Illuminate\Database\Eloquent\Builder random(int $count = 1)
 */
abstract class BaseModel extends Model
{
    use Cacheable;
    /**
     * The format of date
     *
     * @var string
     */
    protected $date_format = 'Y-m-d';

    /**
     * Scope a query to take random row(s).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $count Count of random rows
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRandom($query, $count = 1)
    {
        return $query->orderBy(DB::raw(config('database.connections.' . config('database.default') . '.rand_func')))
            ->take($count);
    }

    /**
     * {@inheritDoc}
     */
    public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null)
    {
        $relation_name = '';

        if (is_null($relation)) {
            list(, $caller) = debug_backtrace(false, 2);

            $relation_name = $caller['function'];
        }
        if ($foreignKey === null) {
            $foreignKey = snake_case(class_basename($related)) . '_id';
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

    public function without(...$relations)
    {
        $this->with = array_except($this->with, $relations);

        return $this;
    }
}
