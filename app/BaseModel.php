<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

abstract class BaseModel extends Model
{
    private $db_rand_func;

    public function scopeRandom($query, $count = 1)
    {
        if (!isset($this->db_rand_func)) {
            switch (config('database.default')) {
                case 'mysql':
                    $this->db_rand_func = 'RAND()';
                    break;
                case 'sqlite':
                    $this->db_rand_func = 'RANDOM()';
                    break;
                case 'pgsql':
                    $this->db_rand_func = 'RANDOM()';
                    break;
                case '':
                    $this->db_rand_func = 'NEWID()';
                    break;
                default:
                    $this->db_rand_func = 'RAND()';
                    break;
            }
        }
        return $query->orderBy(DB::raw($this->db_rand_func))->take($count);
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


    //TODO: переопределить связи с учётом внешних ключей
}
