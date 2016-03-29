<?php namespace NetForceWS\Support;

class Num
{
    /**
     * Retorna o percentual de um total com base em uma parte.
     *
     * @param $total
     * @param $part
     * @param int $round
     *
     * @return float|int
     */
    public static function percentage($total, $part, $round = 2)
    {
        if ($total <= 0.0000) {
            return 0;
        }

        return (round($part * 100.00 / $total, $round));
    }

    /**
     * Retorna o valor de uma percentual com base no total.
     *
     * @param $total
     * @param $perc
     * @param int $round
     *
     * @return float
     */
    public static function percent($total, $perc, $round = 2)
    {
        return (round($total * $perc / 100, $round));
    }

    /**
     * Formata um numero em string.
     *
     * @param $value
     * @param int $dec
     *
     * @return string
     */
    public static function format($value, $dec = 2)
    {
        $dec = intval($dec);

        return number_format($value, $dec, ',', '.');
    }

    /**
     * Converte string em float.
     *
     * @param $str
     * @param bool|int $round
     *
     * @return float
     */
    public static function value($str, $round = false)
    {
        if (is_string($str)) {
            $str = strtr($str, '.', '');
            $str = strtr($str, ',', '.');
        }

        $val = floatval($str);

        if ($round !== false) {
            $val = round($val, intval($round));
        }

        return $val;
    }
}
