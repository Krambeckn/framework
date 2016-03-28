<?php namespace NetForceWS\Database\Schema\Grammars;


class MySqlGrammar extends \Illuminate\Database\Schema\Grammars\MySqlGrammar
{
    /**
     * Determina se schema/database existe
     * @return string
     */
    public function compileSchemaExists()
    {
        return "select * from information_schema.schemadata where (schema_name = ?)";
    }

    /**
     * Determina se visao existe
     * @return string
     */
    public function compileViewExists()
    {
        return "select * from information_schema.views where (table_schema = ?) and (table_name = ?)";
    }

    /**
     * Determina se user existe
     * @return string
     */
    public function compileUserExists()
    {
        return "select * from mysql.user where (User = ?)";
    }

    /**
     * Criar usuario
     * @return string
     */
    public function compileUserCreate($database, $user, $pass, $local = '%')
    {
        return "grant all on `$database`.* TO `$user`@`$local` IDENTIFIED BY '$pass'";
    }

    /**
     * Excluir usuario
     * @return string
     */
    public function compileUserDrop($user, $local = '%')
    {
        return "drop user `$user`@`$local`";
    }

    /**
     * Retornar informacoes de uma tabela
     * @return string
     */
    public function compileGetTable()
    {
        $sql  = "select table_name, engine, table_type";
        $sql .= " from information_schema.tables";
        $sql .= " where (table_schema = ?)";
        $sql .= " and (table_name = ?)";
        $sql .= " order by table_name desc limit 0, 1";

        return $sql;
    }

    /**
     * Retorna lista de campos de uma tabela
     * @return string
     */
    public function compileGetColumns()
    {
        $sql  = "select table_name, column_name, ordinal_position, is_nullable, data_type, character_maximum_length, column_comment";
        $sql .= " from information_schema.columns";
        $sql .= " where (table_schema = ?) and (table_name = ?)";
        $sql .= " order by ordinal_position";

        return $sql;
    }

    /**
     * Criar visao
     * @return string
     */
    public function compileCreateView($name, $sql)
    {
        return "create view " . $name . " as " . $sql;
    }

    /**
     * Excluir visao
     * @return string
     */
    public function compileDropView($name)
    {
        return "drop view " . $name;
    }

    /**
     * Montar select para view de tabelas multi-inquilino
     * @return string
     */
    public function compileSelectViewTenant($columns, $table, $column_tenant)
    {
        $sql  = "select " . $columns . " from " . $table;
        $sql .= " where (" . $column_tenant . " = substring_index(user(), '@', 1))";
        return $sql;
    }

    /**
     * Criar trigger
     * @param $table
     * @return string
     */
    public function compileCreateTriggerTenant($table, $column_tenant)
    {
        $trigger = sprintf('%s_tg', $table);
        $table   = sprintf('%s_tb', $table);

        $sql  = "create trigger " . $trigger;
        $sql .= " before insert on " . $table;
        $sql .= " for each row set new." . $column_tenant . " = if ((new." . $column_tenant . " is null) or (new." . $column_tenant . " = ''), substring_index(user(), '@', 1), new." . $column_tenant . ")";

        return $sql;
    }
}