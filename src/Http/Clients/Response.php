<?php namespace NetForceWS\Http\Clients;

class Response
{
    protected $accept  = '';
    protected $headers = null;
    protected $body    = null;

    public function __construct($headers, $body, $accept)
    {
        $this->headers = $headers;
        $this->body    = $body;
        $this->accept  = $accept;
    }

    /**
     * Get HTTP code
     * @return int
     */
    public function code()
    {
        return $this->header('http_code', 503);
    }

    /**
     * Get HTTP header
     * @param $key
     * @param mixed $default
     * @return mixed
     */
    public function header($key, $default = null)
    {
        return (isset($this->headers[$key])) ? $this->headers[$key] : $default;
    }

    /**
     * Get HTTP body content
     * @return string
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * Make response by http response and content type
     * @return mixed|\SimpleXMLElement
     * @throws \Exception
     */
    public function make()
    {
        $content_type = $this->accept;

        // Get content and charSet
        if (preg_match('%^([a-zA-Z0-9//\\-]+)(.*?(charset=([a-zA-Z0-9\\-]+)).*?)?$%m', trim($this->headers['content_type']), $parts))
            $content_type = (isset($parts[1]) ? $parts[1] : $content_type);

        // Process content
        switch ($content_type)
        {
            case 'application/json':
                $ret = json_decode($this->body);

                // IF error
                if (isset($ret->error))
                {
                    $message = isset($ret->error->message) ? $ret->error->message : '';
                    $code    = isset($ret->error->code)    ? $ret->error->code : 0;
                    error($message, $code);
                }
                return $ret;
                break;

            case 'application/xml':
                $ret = simplexml_load_string($this->body);

                // IF Error
                if (isset($ret->error))
                {
                    $message = isset($ret->error->message) ? $ret->error->message : '';
                    $code    = isset($ret->error->code)    ? $ret->error->code : 0;
                    error($message, $code);
                }
                return $ret;
                break;

            default :
                return $this->body;
                break;
        }
    }

    /**
     * Lista de headers padrão conforme mode
     * @param $mode
     * @return array
     */
    public static function defaultHeaders($mode)
    {
        if ($mode != 'api')
            return [];
        return ['Content-Type' => 'application/json; charset=utf-8'];
    }
}