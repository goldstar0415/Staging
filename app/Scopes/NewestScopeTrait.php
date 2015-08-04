<?php


namespace App\Scopes;

trait NewestScopeTrait
{
    /**
     * Boot the scope.
     *
     * @return void
     */
    public static function bootNewestTrait()
    {
        static::addGlobalScope(new NewestScope);
    }

    /**
     * Get the query builder without the scope applied.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withoutOrdering()
    {
        return with(new static)->newQueryWithoutScope(new NewestScope);
    }
}
