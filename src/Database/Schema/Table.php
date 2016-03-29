<?php

namespace NetForceWS\Database\Schema;

class Table extends \Illuminate\Database\Schema\Blueprint
{
    const Key = 'key';
    const String = 'string';
    const Extend = 'extend';
    const Integer = 'integer';
    const Number = 'number';
    const Boolean = 'boolean';
    const Datetime = 'datetime';
    const Date = 'date';
    const Time = 'time';
    const Text = 'text';
    const Association = 'association';
    const Options = 'options';

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * Flag se estrutura tem controle de inquilinos.
     *
     * @var bool
     */
    protected $enabledTenant = null;

    /**
     * @param Builder $builder
     * @param
     * @param $table
     * @param callable $callback
     */
    public function __construct(Builder $builder, $table, \Closure $callback = null)
    {
        $this->builder = $builder;
        $this->table = $table;
        parent::__construct($table, $callback);
    }

    /**
     * Criar campo primario padrao.
     */
    public function key()
    {
        $col = $this->bigIncrements(self::keyAttr());

        return $this->setTypeColumn($col, self::keyAttr(), self::Key);
    }

    /**
     * Criar campo primario padrao.
     */
    public function uniqueKey($column, $length = 255)
    {
        $col = $this->string($column, $length);
        $this->primary($column);

        return $this->setTypeColumn($col, $column, self::String);
    }

    /**
     * Campo de extensao.
     */
    public function extend($table)
    {
        // Criar campo
        $col = $this->bigInteger(self::keyAttr(), false, true);
        $col->nullable(true);
        $col->unsigned(true);

        // Criar constrain
        $fk = $this->foreign(self::keyAttr());
        $fk->on($table);
        $fk->references(self::keyAttr());

        // Primario
        $this->primary(self::keyAttr());

        return $this->setTypeColumn($col, self::keyAttr(), self::Extend);
    }

    /**
     * Campo String (varchar).
     */
    public function string($column, $length = 255)
    {
        $col = parent::string(strtolower($column), $length);
        $col->nullable(true);

        return $this->setTypeColumn($col, $column, self::String);
    }

    /**
     * Campo Inteiro.
     */
    public function integer($column, $unsigned = false, $autoIncrement = false)
    {
        $col = parent::integer(strtolower($column), $autoIncrement, $unsigned);
        $col->default(0);

        return $this->setTypeColumn($col, $column, self::Integer);
    }

    /**
     * Campo Numero.
     */
    public function number($column, $total = 20, $places = 5)
    {
        $col = $this->decimal(strtolower($column), $total, $places);
        $col->default(0);

        return $this->setTypeColumn($col, $column, self::Number);
    }

    /**
     * Campo Logico.
     */
    public function boolean($column)
    {
        $col = $this->tinyInteger(strtolower($column), false, true);
        $col->default(0);

        return $this->setTypeColumn($col, $column, self::Boolean);
    }

    /**
     * Campo DATA e HORA.
     */
    public function dateTime($column)
    {
        $col = parent::dateTime(strtolower($column));
        $col->nullable(true);

        return $this->setTypeColumn($col, $column, self::Datetime);
    }

    /**
     * Campo DATA.
     */
    public function date($column)
    {
        $col = parent::date(strtolower($column));
        $col->nullable(true);

        return $this->setTypeColumn($col, $column, self::Date);
    }

    /**
     * Campo HORA.
     */
    public function time($column)
    {
        $col = parent::time(strtolower($column));
        $col->nullable(true);

        return $this->setTypeColumn($col, $column, self::Time);
    }

    /**
     * Campo TEXTO.
     */
    public function text($column)
    {
        $col = parent::text(strtolower($column));
        $col->nullable(true);

        return $this->setTypeColumn($col, $column, self::Text);
    }

    /**
     * Campo de associacao (Lookup).
     */
    public function association($column, $table)
    {
        // Verificar se deve adicionar o sufixo
        if (ForeignKey::isAssociation($column) != true) {
            $column = sprintf('%s_%s', strtolower($column), self::keyAttr());
        }

        // Carregar informacao da tabela
        $table = $this->builder->getTable($table);

        // Criar campo
        $col = $this->bigInteger($column, false, true);
        $col->nullable(true);
        $col->unsigned(true);

        // Criar constrain
        $fk_name = ForeignKey::makeName($this->table, $column);
        $fk = $this->foreign($column, $fk_name);
        $fk->on($table->name);
        $fk->references(self::keyAttr());

        return $this->setTypeColumn($col, $column, self::Association);
    }

    /**
     * Campos quando tabela mult-tenant.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function tenant($ignore = true)
    {
        // Verificar se tabela de inquilino foi criada
        if ($this->enabledTenant() != true) {
            if ($ignore)
                return new \Illuminate\Support\Fluent();
            error('Not enabled multi tenant control');
        }

        // Criar campo
        return $this->association(self::tenantAttr(), self::tenantTable());
    }

    /**
     * Campo LISTA.
     */
    public function options($column)
    {
        $col = parent::string($column, 5);
        $col->nullable(true);

        return $this->setTypeColumn($col, $column, self::Options);
    }

    /**
     * Excluir campo.
     *
     * @param  string|array $columns
     *
     * @return \Illuminate\Support\Fluent
     */
    public function dropColumn($columns)
    {
        $columns = is_array($columns) ? $columns : (array)func_get_args();

        // Verificar se deve excluir constrain dos campos lookups antes
        foreach ($columns as $column) {
            if (ForeignKey::isAssociation($column)) {
                parent::dropForeign(ForeignKey::makeName($this->table, $column));
            }
        }

        return parent::dropColumn($columns);
    }

    /**
     * Set type field in comment.
     *
     * @param \Illuminate\Support\Fluent $column
     * @param $name
     * @param $type
     *
     * @return \Illuminate\Support\Fluent $column
     */
    protected function setTypeColumn(\Illuminate\Support\Fluent $column, $name, $type)
    {
        $column->comment($type);

        return $column;
    }

    /**
     * Nome do atributo KEY.
     *
     * @return string
     */
    public static function keyAttr()
    {
        return app('config')->get('builder.key_attr', 'id');
    }

    /**
     * Nome da tabela de inquilinos.
     *
     * @return string
     */
    public static function tenantTable()
    {
        return app('config')->get('builder.tenant.table', 'inquilinos');
    }

    /**
     * Nome do atributo inquilino.
     *
     * @return string
     */
    public static function tenantAttr()
    {
        return app('config')->get('builder.tenant.attribute', 'inquilino');
    }

    /**
     * Nome do campo inquilino na tabela.
     *
     * @return string
     */
    public static function tenantField()
    {
        return sprintf('%s_%s', self::tenantAttr(), self::keyAttr());
    }

    /**
     * Verificar se tem controle de inquilino.
     *
     * @return bool
     */
    public function enabledTenant()
    {
        if (is_null($this->enabledTenant) != true) {
            return ($this->enabledTenant == true);
        }

        return $this->enabledTenant = $this->builder->hasTable(self::tenantTable());
    }
}