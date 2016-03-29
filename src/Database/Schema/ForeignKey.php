<?php namespace NetForceWS\Database\Schema;

class ForeignKey
{
    public static function makeName($table, $column)
    {
        // Verificar se deve remover sufixo do nome lookup
        $sufix = sprintf('_%s', Table::keyAttr());
        if (preg_match('/\\A([a-zA-Z0-9_]+)' . $sufix . '\\z/', $column, $parts)) {
            $column = $parts[1];
        }

        $table = strtolower($table);
        $column = strtolower($column);

        return sprintf('fk_%s_%s', $table, $column);
    }

    /**
     * Retorna se uma campo eh uma associacao.
     *
     * @param $name
     *
     * @return bool
     */
    public static function isAssociation($name)
    {
        $sufix = sprintf('%s_%s', $name, Table::keyAttr());
        return preg_match('/\\A([a-zA-Z0-9_]+)' . $sufix . '\\z/', $name);
    }
}