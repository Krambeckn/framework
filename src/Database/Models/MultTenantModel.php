<?php namespace NetForceWS\Database\Models;

use NetForceWS\Database\Models\Scopes\TenantScope;
use NetForceWS\Database\Schema\Table;

trait MultTenantModel
{
    public $multTenant = false;

    /**
     * Boot do trait.
     */
    public static function bootMultTenantModel()
    {
        // Adicionar scopo
        static::addGlobalScope(new TenantScope());

        // Informar tenant
        static::saving(function ($model) {
            if (isset($model->multTenant) && ($model->multTenant == true)) {
                // Verificar se inquilino já foi informado
                if (array_key_exists(Table::tenantField(), $model->attributes)) {
                    return;
                }

                // Verificar se usuário esta logado
                if (\Auth::check() != true) {
                    error('Usuário não esta logado');
                }

                // Setar inquilino
                $model->{Table::tenantField()} = \Auth::user()->{Table::tenantField()};
            }
        });
    }
}