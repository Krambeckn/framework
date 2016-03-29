<?php namespace NetForceWS\Database\Facades;

use NetForceWS\Database\Schema\Builder;

/**
 * @see \Illuminate\Database\Schema\Builder
 */
class Schema extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get a schema builder instance for a connection.
     *
     * @param string $name
     *
     * @return \NetForceWS\Database\Schema\Builder
     */
    public static function connection($name)
    {
        $con = static::$app['db']->connection($name);
        $con->useDefaultSchemaGrammar();

        return new Builder($con);
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        $con = static::$app['db']->connection();
        $con->useDefaultSchemaGrammar();

        return new Builder($con);
    }
}