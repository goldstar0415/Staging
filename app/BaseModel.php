<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

abstract class BaseModel extends Model
{

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
        if ($foreignKey === null) {
            $foreignKey = snake_case(class_basename(SpotTypeCategory::class)) . '_id';
        }
        return parent::belongsTo($related, $foreignKey, $otherKey, $relation);
    }
}
