<?php namespace NetForceWS\Clients;

class Rest
{
    protected $method = '';
    protected $url = '';
    protected $params = [];
    protected $header = [];
    public $sendcookie = true;

    public function __construct()
    {
        $this->reset();
    }

    /**
     * Set param of request.
     *
     * @param $name
     * @param null $value
     *
     * @return Rest
     */
    public function with($name, $value = null)
    {
        if ($name === false) {
            return $this;
        }

        if (is_array($name) && is_null($value)) {
            foreach ($name as $n => $v) {
                $this->with($n, $v);
            }

            return $this;
        }

        $this->params[$name] = $value;

        return $this;
    }

    /**
     * Set header of request.
     *
     * @param $name
     * @param null $value
     *
     * @return Rest
     */
    public function header($name, $value = null)
    {
        if (is_array($name) && is_null($value)) {
            foreach ($name as $n => $v) {
                $this->header($n, $v);
            }

            return $this;
        }

        $this->header[$name] = $value;

        return $this;
    }

    /**
     * Set format accept.
     *
     * @param $format
     *
     * @return Rest
     */
    public function accept($format)
    {
        return $this->header('Accept', $format);
    }

    /**
     * Set URL request.
     *
     * @param $url
     *
     * @return Rest
     */
    public function url($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Request METHOD.
     *
     * @param $method
     * @param $url
     * @param array $params
     *
     * @return Rest
     */
    public function method($method, $url, $params = false)
    {
        $this->method = strtoupper($method);

        return $this->url($url)->with($params);
    }

    /**
     * Request GET method.
     *
     * @param $url
     * @param array $params
     *
     * @return Rest
     */
    public function get($url, $params = false)
    {
        return $this->method('GET', $url, $params);
    }

    /**
     * Request POST method.
     *
     * @param $url
     * @param array $params
     *
     * @return Rest
     */
    public function post($url, $params = false)
    {
        return $this->method('POST', $url, $params);
    }

    /**
     * Request PUT method.
     *
     * @param $url
     * @param array $params
     *
     * @return Rest
     */
    public function put($url, $params = false)
    {
        return $this->method('PUT', $url, $params);
    }

    /**
     * Request DELETE method.
     *
     * @param $url
     *
     * @return Rest
     */
    public function delete($url)
    {
        return $this->method('DELETE', $url);
    }

    /**
     * Execute request CURL.
     */
    protected function call()
    {
        global $_COOKIE;

        $url = $this->url;

        // Make headers
        $headers = [];
        foreach ($this->header as $hk => $hv) {
            $headers[] = sprintf('%s: %s', $hk, $hv);
        }

        // Options request cURL
        $opts = [];
        $opts[CURLOPT_RETURNTRANSFER] = true; // Hide return
        $opts[CURLOPT_AUTOREFERER] = true; // Follow redirects
        $opts[CURLOPT_FOLLOWLOCATION] = true; // Follow redirects too
        $opts[CURLOPT_HEADER] = false; // Extract headers
        $opts[CURLOPT_SSL_VERIFYPEER] = true; // This make sure it fail on SSL CA
        $opts[CURLOPT_HTTPHEADER] = $headers; // Headers

        // Send cookies
        if ($this->sendcookie) {
            // remove XDEBUG_SESSION
            $cookie = $_COOKIE;
            if (array_key_exists('XDEBUG_SESSION', $cookie)) {
                unset($cookie['XDEBUG_SESSION']);
            }
            $opts[CURLOPT_COOKIE] = http_build_query($cookie, null, ';');
        }

        // Method
        switch ($this->method) {
            case 'POST':
                $opts[CURLOPT_POST] = true;
                $opts[CURLOPT_POSTFIELDS] = $this->params;
                $opts[CURLOPT_URL] = $url;
                break;

            case 'GET':
                $opts[CURLOPT_HTTPGET] = true;
                $opts[CURLOPT_URL] = url($url, $this->params);
                break;

            case 'PUT':
                $opts[CURLOPT_PUT] = true;
                $opts[CURLOPT_URL] = url($url, $this->params);
                break;

            case 'DELETE':
                $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                $opts[CURLOPT_URL] = $url;
                break;

            case '':
                error('RestClient: Method was not informed');
                break;

            default:
                error('RestClient: Method %s not implemented', $this->method);
                break;
        }

        // cURL call
        $cr = curl_init();
        curl_setopt_array($cr, $opts);
        $resp_body = curl_exec($cr);

        // Has error request
        if (curl_errno($cr)) {
            $e = new \Exception(curl_error($cr), curl_errno($cr));
            curl_close($cr);
            throw $e;
        }

        // Get response header
        $resp_header = curl_getinfo($cr);
        curl_close($cr);

        $response = new Response($resp_header, $resp_body, $this->header['Accept']);
        $this->reset();

        return $response->make();
    }

    /**
     * Load initialization values.
     */
    protected function reset()
    {
        $this->method = '';
        $this->url = '';
        $this->params = [];
        $this->header = [];
        $this->accept('application/json');
    }

    /**
     * Call and return JSON object.
     *
     * @return object
     */
    public function json()
    {
        $this->accept('json');

        return $this->call();
    }

    /**
     * Call and return XML object.
     *
     * @return \SimpleXMLElement
     */
    public function xml()
    {
        $this->accept('xml');

        return $this->call();
    }

    /**
     * Call and return string HTML.
     *
     * @return string
     */
    public function html()
    {
        $this->accept('html');

        return $this->call();
    }

    /**
     * Alias para HTML.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->html();
    }
}