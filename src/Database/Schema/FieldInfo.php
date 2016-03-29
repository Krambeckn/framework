<?php namespace NetForceWS\Database\Schema;

class FieldInfo
{
    public $table = '';
    public $name = '';
    public $type = '';

    public static $typesText = ['string', 'text', 'options', 'datetime', 'date', 'time', 'boolean'];

    public function __construct($table, $name, $type)
    {
        $this->table = $table;
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Retorna se campo e do tipo alfa numerico.
     *
     * @return bool
     */
    public function isText()
    {
        return in_array($this->type, self::$typesText);
    }
}