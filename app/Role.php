<?php

namespace App;

use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{
    public static function take($role_name)
    {
        return self::where('name', $role_name)->first();
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
