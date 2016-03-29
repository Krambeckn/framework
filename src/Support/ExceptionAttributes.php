<?php namespace NetForceWS\Support;

class ExceptionAttributes extends \Exception
{
    protected $attrs = [];

    /**
     * Criar excessao.
     *
     * @param string $message
     * @param int $code
     * @param array $attrs
     * @param \Exception $previous
     */
    public function __construct($message = '', $code = 0, array $attrs = [], \Exception $previous = null)
    {
        $this->attrs = $attrs;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Retorna as mensagens dos atributos.
     *
     * @return array
     */
    public function getAttrs()
    {
        return $this->attrs;
    }

    public function toMessageStr()
    {
        $lines = [];
        foreach ($this->attrs as $attr => $msgs) {
            $lines[] = sprintf("%s: %s\r\n", $attr, implode('. ', $msgs));
        }

        return sprintf("%s\r\n%s", $this->getMessage(), implode("\r\n", $lines));
    }
}