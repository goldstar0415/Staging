<?php


namespace App\Scopes;

use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ScopeInterface;

class NewestScope implements ScopeInterface
{
    /**
     * Apply scope on the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $column = $model->getCreatedAtColumn();
        $builder->orderBy($column, 'DESC');
        $this->addWithoutNewest($builder);
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
        $column = $model->getCreatedAtColumn();
        foreach ((array)$query->orders as $key => $order) {
            if ($this->isNewestConstraint($order, $column)) {
                $this->removeOrder($query, $key);
            }
        }
    }

    /**
     * Remove scope constraint from the query.
     *
     * @param BaseBuilder $query
     * @param  int $key
     * @internal param BaseBuilder $builder
     */
    protected function removeOrder(BaseBuilder $query, $key)
    {
        $property = $query->unions ? 'unionOrders' : 'orders';
        unset($query->{$property}[$key]);
        $query->$property = array_values($query->$property);

        if (empty($query->$property)) {
            $query->$property = null;
        }
    }

    /**
     * Check if given where is the scope constraint.
     *
     * @param  array $order
     * @param  string $column
     * @return boolean
     */
    protected function isNewestConstraint(array $order, $column)
    {
        return ($order['column'] == $column && $order['direction'] == 'desc');
    }

    /**
     * Extend Builder with custom method.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     */
    protected function addWithoutNewest(Builder $builder)
    {
        $builder->macro(
            'withoutNewest',
            function (Builder $builder) {
                $this->remove($builder, $builder->getModel());
                return $builder;
            }
        );
    }
}
