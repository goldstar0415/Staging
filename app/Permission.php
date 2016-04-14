<?php

namespace App;

use App\Extensions\Cache\Cacheable;
use Zizaco\Entrust\EntrustPermission;

/**
 * Model Permission
 * @package App
 */
class Permission extends EntrustPermission
{
    use Cacheable;

    public function __construct(array $attributes = [])
    {
        $this->cacheFull = true;
        parent::__construct($attributes);
    }

    /**
     * Relations which needs to flush from cache
     * @return array
     */
    public function flushRelations()
    {
        return [
            'roles'
        ];
    }
}
