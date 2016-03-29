<?php namespace NetForceWS\Support;

use DateTime;
use Carbon\Carbon;

trait Properties
{
    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $casts = [];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Verificar se propriedade foi implementada.
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getAttributeValue($name);
    }

    /**
     * Setar propriedade.
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $method_name = 'set' . studly_case($name) . 'Attribute';

        // Verificar metodo
        if (method_exists($this, $method_name)) {
            call_user_func_array([$this, $method_name], [$name, $value]);
        } else {
            $this->properties[$name] = $value;
        }
    }

    public function getAttributeValue($name)
    {
        $value = (array_key_exists($name, $this->properties)) ? $this->properties[$name] : null;

        // Verificar metodo
        $method_name = 'get' . studly_case($name) . 'Attribute';
        if (method_exists($this, $method_name)) {
            $value = call_user_func_array([$this, $method_name], []);
        }

        // Salvar propriedade
        $this->properties[$name] = $value;

        // Cast
        if (method_exists($this, 'castAttribute') && (array_key_exists($name, $this->casts))) {
            $value = $this->castAttribute($name, $value);
        }

        return $value;
    }

    public function fromJson($value, $asObject = false)
    {
        return json_decode($value, ! $asObject);
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed $value
     *
     * @return \Carbon\Carbon
     */
    protected function asDateTime($value)
    {
        // If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to reinstantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        if ($value instanceof Carbon) {
            return $value;
        }

        // If the value is already a DateTime instance, we will just skip the rest of
        // these checks since they will be a waste of time, and hinder performance
        // when checking the field. We will just return the DateTime right away.
        if ($value instanceof DateTime) {
            return Carbon::instance($value);
        }

        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        // Finally, we will just assume this date is in the format used by default on
        // the database connection and use that format to create the Carbon object
        // that is returned back out to the developers after we convert it here.
        return Carbon::createFromFormat($this->getDateFormat(), $value);
    }

    protected function getDateFormat()
    {
        return $this->dateFormat;
    }
}