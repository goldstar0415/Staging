<?php


namespace App\Scopes;

use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ScopeInterface;

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
            $query->whereNull($this->column)->orWhere($this->column, true);
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

        $query->wheres = collect($query->wheres)->reject(function ($where) use ($column) {
            return $this->isWithRequestedConstraint($where, $column);
        })->values()->all();
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
        return ($where['type'] == 'Null' && $where['column'] == $column
            or $where['column'] == $column && $where['value'] == true);
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
