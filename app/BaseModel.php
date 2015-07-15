<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    private $db_rand_func;

    public function random()
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
        return self::orderBy(DB::raw($this->db_rand_func))->take(1)->first();
    }

    //TODO: переопределить связи с учётом внешних ключей
    //TODO: унаследовать модели от данной
}
