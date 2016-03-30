<?php namespace NetForceWS\Support;

class ExceptionAttributes extends \Exception
{
    /**
     * Lista de atributos.
     * @var array
     */
    protected $attrs = [];

    /**
     * Idica de execao ocorreu em um ambiente Console.
     * @var bool
     */
    protected $isConsole = false;

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
        $this->isConsole = app()->runningInConsole();

        $message = $this->isConsole ? self::makeMsgs($message, $attrs) : $message;

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

    /**
     * Retornar a mensagem com as mensagens dos campos se houver.
     *
     * @return string
     */
    public function toMessageStr()
    {
        return $this->isConsole ? $this->getMessage() : self::makeMsgs($this->getMessage(), $this->getAttrs());
    }

    /**
     * Monta a mensagem com os atributos.
     *
     * @param $message
     * @param $attrs
     *
     * @return string
     */
    protected static function makeMsgs($message, $attrs)
    {
        $lines = [];
        foreach ($attrs as $attr => $msgs) {
            $lines[] = sprintf("%s: %s\r\n", $attr, implode('. ', $msgs));
        }

        return sprintf("%s\r\n%s", $message, implode("\r\n", $lines));
    }
}