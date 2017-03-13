<?php


namespace App\Scopes;

trait NewestScopeTrait
{
    /**
     * Boot the scope.
     *
     * @return void
     */
    public static function bootNewestScopeTrait()
    {
        static::addGlobalScope(new NewestScope);
    }

    /**
     * Get the query builder without the scope applied.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withoutNewest()
    {
        return with(new static)->newQueryWithoutScope(new NewestScope);
    }
}
