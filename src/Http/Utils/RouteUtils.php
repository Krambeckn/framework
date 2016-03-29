<?php namespace NetForceWS\Http\Utils;

use NetForceWS\Support\Str;
use \Route;

class RouteUtils
{
    /**
     * Retorna o utltimo id informado.
     * @return mixed
     */
    public static function getRouteId($name = null)
    {
        $route = ($name === null) ? Route::current() : Route::getRoutes()->getByName($name);
        $last = Str::last(Str::before($route->uri()));
        $id_name = Str::startsWith($last, '{') ? str_replace(['{', '}'], '', $last) : sprintf('%s_id', $last);

        return Route::input($id_name);
    }

    /**
     * Retorna a URL pela rota.
     *
     * @return string
     */
    public static function getRouteUri($name)
    {
        $route = Route::getRoutes()->getByName($name);
        $uri = $route->uri();
        $params = Route::current()->parameters();

        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }

        return url($uri);
    }
}
