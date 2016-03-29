<?php

if (!function_exists('error')) {
    /**
     * Create exception mult-language and with params.
     *
     * @param $msg
     * @return bool
     * @throws Exception
     */
    function error($msg)
    {
        // Se msg for um objeto ou array deve fazer um print_r
        if (is_object($msg) || is_array($msg)) {
            $msg = print_r($msg, true);
        }

        $args = func_get_args();
        $args[0] = $msg;
        $msg = trim(call_user_func_array('sprintf', $args));

        $code = $args[count($args) - 1];

        if (is_numeric($code)) {
            throw new \Exception($msg, $code);
        } else {
            throw new \Exception($msg);
        }
    }
}