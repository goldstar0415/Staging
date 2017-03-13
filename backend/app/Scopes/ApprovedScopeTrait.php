<?php

namespace App\Scopes;

trait ApprovedScopeTrait
{
    /**
     * Boot the scope.
     *
     * @return void
     */
    public static function bootApprovedScopeTrait()
    {
        static::addGlobalScope(new ApprovedScope);
    }

    /**
     * Get the query builder without the scope applied.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withRequested()
    {
        return with(new static)->newQueryWithoutScope(new ApprovedScope);
    }
}
