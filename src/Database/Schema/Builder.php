<?php namespace NetForceWS\Database\Schema;

use Closure;
use Illuminate\Database\Connection;
use NetForceWS\Database\Schema\Grammars\MySqlGrammar;

class Builder extends \Illuminate\Database\Schema\MySqlBuilder
{
    /**
     * Create a new database Schema manager.
     *
     * @param  \Illuminate\Database\Connection $connection
     *
     * @return void
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->grammar = new MySqlGrammar();
    }

    /**
     * @param string $table
     * @param callable $callback
     *
     * @return \Illuminate\Database\Schema\Blueprint|mixed|Table
     */
    protected function createBlueprint($table, Closure $callback = null)
    {
        if (isset($this->resolver)) {
            return call_user_func($this->resolver, $table, $callback);
        } else {
            return new Table($this, $table, $callback);
        }
    }

    /**
     * Criar tabela.
     */
    public function create($table, Closure $callback)
    {
        $blueprint = $this->createBlueprint($table);

        // Definir InnoDB como padrao
        $blueprint->create();
        $blueprint->engine = 'InnoDB';

        $callback($blueprint);

        $this->build($blueprint);
    }

    /**
     * Excluir tabela.
     */
    public function drop($table)
    {
        // Ignorar se nao existir
        $this->dropIfExists($table);
    }

    /**
     * Retorna se um database/schema existe.
     *
     * @param  string $table
     *
     * @return bool
     */
    public function hasSchema($schema)
    {
        $sql = $this->grammar->compileSchemaExists();

        return count($this->connection->select($sql, [$schema])) > 0;
    }

    /**
     * Retorna informações da definição da tabela (estrutura).
     *
     * @param $table
     *
     * @return bool|\stdClass
     */
    public function getTable($table)
    {
        $sql = $this->grammar->compileGetTable();
        $database = $this->connection->getDatabaseName();
        $table = $this->connection->getTablePrefix() . $table;

        if (! ($row = $this->connection->selectOne($sql, [$database, $table]))) {
            return false;
        }

        $row->engine = (strtoupper($row->engine) == 'MEMORY') ? TableInfo::Memory : TableInfo::Storage;
        $row->table_type = strtoupper($row->table_type);

        return new TableInfo($this, $table, $row->engine);
    }

    /**
     * Retorna a lista de campos de uma tabela.
     *
     * @param $table
     *
     * @return array
     */
    public function getColumns($table, $filterFields = false)
    {
        $sql = $this->grammar->compileGetColumns();
        $database = $this->connection->getDatabaseName();
        $table = $this->connection->getTablePrefix() . $table;
        $list = [];

        $columns = $this->connection->select($sql, [$database, $table]);
        foreach ($columns as $col) {
            if ((is_array($filterFields) && in_array('', $filterFields)) || ($filterFields === false)) {
                $fld = new FieldInfo(trim($col->table_name), trim($col->column_name), trim($col->column_comment));
                $list[$fld->name] = $fld;
            }
        }

        return $list;
    }
}