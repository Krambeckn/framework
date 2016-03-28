<?php namespace NetForceWS\Validation;

class Validators
{
    /**
     * Validacao ID
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return int
     */
    public function id($attribute, $value, $parameters)
    {
        return preg_match('/^[a-z]+[a-z0-9]*$/i', $value);
    }

    /**
     * Validacao Route
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return int
     */
    public function route($attribute, $value, $parameters)
    {
        return preg_match('/^[a-z]+[a-z0-9]*$/', $value);
    }

    /**
     * Validacao: DOMAIN
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return int
     */
    public function domain($attribute, $value, $parameters)
    {
        return preg_match('/^[a-z]+[a-z0-9._]*$/', $value);
    }

    /**
     * Validacao: checkpass
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return int
     */
    public function checkpass($attribute, $value, $parameters)
    {
        if (\Auth::check() != true)
            return false;

        // Validar
        $user        = \Auth::user();
        $credentials = ['email' => $user->email, 'password' => $value];
        return \Auth::validate($credentials);
    }

    /**
     * Convertes os parametros da regra {param}
     * @param array $values
     * @param array $rules
     * @return array Rules
     */
    public static function translateParams(array $values, array $rules)
    {
        $new_rules = [];

        // Tratar variaveis da regra
        foreach ($rules as $field => $expr)
        {
            preg_match_all('/{([a-zA-Z0-9_]+)+}/', $expr, $vars, PREG_PATTERN_ORDER);
            foreach ($vars[1] as $i => $var_id)
            {
                $var_old = $vars[0][$i];
                if (array_key_exists($var_id, $values))
                    $expr = str_replace($var_old, $values[$var_id], $expr);
            }

            $new_rules[$field] = $expr;
        }

        return $new_rules;
    }
}