<?php

namespace NetForceWS\Database\Schema;

class TableInfo
{
    const Storage = 'storage';
    const Memory = 'memory';

    /**
     * @var Builder
     */
    protected $builder = null;

    /**
     * Flag se tabela eh mult-inquilina
     * @var bool|null
     */
    protected $multTenant = null;

    /**
     * Nome da tabela
     * @var string
     */
    public $name = '';

    /**
     * Tipo de armazenamento
     * @var string
     */
    public $type = '';

    public function __construct(Builder $builder, $name, $type = self::Storage)
    {
        $this->builder = $builder;
        $this->name = $name;
        $this->type = $type;
        $this->multTenant = null;
    }

    /**
     * Retorna se tabela eh mult-inquilino.
     *
     * @return bool
     */
    public function isMultTenant()
    {
        if ($this->multTenant !== null)
            return ($this->multTenant == true);
        return $this->multTenant = $this->builder->hasColumn($this->name, Table::tenantField());
    }
}