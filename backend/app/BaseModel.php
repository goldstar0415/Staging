<?php

namespace App;

use App\Extensions\Cache\BelongsToManyCache;
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

    public function belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null)
    {
        // If no relationship name was passed, we will pull backtraces to get the
        // name of the calling function. We will use that function name as the
        // title of this relation since that is a great convention to apply.
        if (is_null($relation)) {
            $relation = $this->getBelongsToManyCaller();
        }

        // First, we'll need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we'll make the query
        // instances as well as the relationship instances we need for this.
        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $instance = new $related;

        $otherKey = $otherKey ?: $instance->getForeignKey();

        // If no table name was provided, we can guess it by concatenating the two
        // models using underscores in alphabetical order. The two model names
        // are transformed to snake case from their default CamelCase also.
        if (is_null($table)) {
            $table = $this->joiningTable($related);
        }

        // Now we're ready to create a new query builder for the related model and
        // the relationship instances for the relation. The relations will set
        // appropriate query constraint and entirely manages the hydrations.
        $query = $instance->newQuery();

        return new BelongsToManyCache($query, $this, $table, $foreignKey, $otherKey, $relation);
    }
}
