<?php namespace NetForceWS\Support;

class Arr extends \Illuminate\Support\Arr
{
    public static function toPhpFile($array)
    {
        $code  = '<?php' . "\r\n";
        $code .= 'return [' . "\r\n";
        self::phpFile_item($code, $array);
        $code .= '];';

        return $code;
    }

    protected static function phpFile_item(&$code, $array, $ident = 4)
    {
        $sident = str_pad('', $ident, ' ');
        foreach ($array as $key => $value)
        {
            $code .= $sident;
            $code .= '\'' . $key . '\' => ';

            if (is_array($value))
            {
                $code .= '[' . "\r\n";
                self::phpFile_item($code, $value, $ident + 4);
                $code .= $sident;
                $code .= '],' . "\r\n";
            } else {
                $value = str_replace("\r\n", "\n\r" . $sident, $value);
                $value = str_replace("\n\r", "\r\n", $value);
                $code .= '"' . $value . '",' . "\r\n";
            }
        }
    }
}
