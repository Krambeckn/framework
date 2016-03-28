<?php namespace NetForceWS\Database\Models\Scopes;

use \Illuminate\Database\Eloquent\Builder;
use \Illuminate\Database\Eloquent\Model;
use \NetForceWS\Database\Schema\Table;

class TenantScope implements \Illuminate\Database\Eloquent\Scope
{
    /**
     * @var null|int
     */
    protected $inquilino_id = null;


    public function __construct()
    {
        $this->inquilino_id = (\Auth::check() ? \Auth::user()->{Table::tenantField()} : null);
    }

    /**
     * Aplicar
     * @param Builder $builder
     * @param Model $model
     */
    public function apply(Builder $builder, Model $model)
    {
        if (isset($model->multTenant) && ($model->multTenant == true) && ($this->inquilino_id !== null))
        {
            $builder->where(Table::tenantField(), $this->inquilino_id);
        }
    }
}