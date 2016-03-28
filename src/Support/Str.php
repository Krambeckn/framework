<?php namespace NetForceWS\Support;

class Str extends \Illuminate\Support\Str
{
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return false | needle
     */
    public static function getStartsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle)
        {
            if ($needle != '' && strpos($haystack, $needle) === 0)
                return $needle;
        }
        return false;
    }

    /**
     * Aplica um formato ao valor ou nos valores de um array
     * @param $value
     * @param $format
     * @return array|string
     */
    public static function format($value, $format)
    {
        // Verificar se value eh um array
        if (is_array($value))
        {
            return array_map(function($val) use ($format) {
                return sprintf($format, $val);
            }, $value);
        }

        return sprintf($format, $value);
    }

    /**
     * Traduz os parâmetros pelos valores de $values
     * @param $mask
     * @param $values
     */
    public static function values($mask, array $values)
    {
        foreach ($values as $k => $v)
        {
            $mask = str_replace('{' . $k . '}', $v, $mask);
        }
        return $mask;
    }

    /**
     * Retorna a string antes da ultima /
     * @param $str
     * @return mixed|string
     */
    public static function before($str)
    {
        $str = str_replace('\\', '/', $str);
        $i   = strrpos($str, '/');
        if ($i === false)
            return $str;
        return substr($str, 0, $i);
    }

    /**
     * Retorna a string depois da ultima /
     * @param $str
     * @return mixed|string
     */
    public static function last($str)
    {
        $str = str_replace('\\', '/', $str);
        $i   = strrpos($str, '/');
        if ($i === false)
            return $str;
        return substr($str, ($i + 1));
    }
}
