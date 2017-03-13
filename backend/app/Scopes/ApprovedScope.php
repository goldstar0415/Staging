<?php


namespace App\Scopes;

use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ScopeInterface;
use Request;

class ApprovedScope implements ScopeInterface
{
    protected $column = 'is_approved';

    /**
     * Apply scope on the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where(function ($query) {
            $query->where($this->column, true);
        });
        $this->addWithRequested($builder);
    }

    /**
     * Remove scope from the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function remove(Builder $builder, Model $model)
    {
        $query = $builder->getQuery();
        $column = $this->column;

        $wheres = [];
        $binding_key = 0;

        foreach ($query->wheres as $key => $where) {
            if ($this->isWithRequestedConstraint($where, $column)) {
                $binding_key = $key;
            } else {
                $wheres[] = $where;
            }
        }
        $bindings = $query->getBindings();
        unset($bindings[$binding_key]);
        $query->setBindings($bindings);
        $query->wheres = $wheres;
    }

    /**
     * Check if given where is the scope constraint.
     *
     * @param  array $where
     * @param  string $column
     * @return boolean
     */
    protected function isWithRequestedConstraint(array $where, $column)
    {
        if ($where['type'] === 'Nested') {
            foreach ($where['query']->wheres as $where) {
                return ($where['type'] == 'Null' && $where['column'] == $column
                    or $where['column'] == $column && $where['value'] == true);
            }
        }

        return false;
    }

    /**
     * Extend Builder with custom method.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     */
    protected function addWithRequested(Builder $builder)
    {
        $builder->macro(
            'withRequested',
            function (Builder $builder) {
                $this->remove($builder, $builder->getModel());
                return $builder;
            }
        );
    }
}
