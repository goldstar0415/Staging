<?php

namespace App;

use App\Extensions\Cache\Cacheable;
use Zizaco\Entrust\EntrustRole;

/**
 * Model Role
 * @package App
 *
 * @property string $name
 * @property string $display_name
 * @property string $description
 *
 * Relation properties
 * @property \Illuminate\Database\Eloquent\Collection $users
 */
class Role extends EntrustRole
{
    use Cacheable;
    
    public function __construct(array $attributes = [])
    {
        $this->cacheFull = true;
        parent::__construct($attributes);
    }

    /**
     * Retrieve a role model by it's name
     * @param string $role_name
     * @return mixed
     */
    public static function take($role_name)
    {
        return self::where('name', $role_name)->first();
    }

    /**
     * The users that belong to the role
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
