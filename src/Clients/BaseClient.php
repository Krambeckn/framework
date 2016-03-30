<?php namespace NetForceWS\Clients;

use Illuminate\Support\Arr;
use NetForceWS\Clients\Common\Rest;

abstract class BaseClient
{
    /**
     * Configuracoes de acesso.
     * @var array
     */
    protected $configs = [];

    /**
     * @var Rest
     */
    protected $rest = null;

    /**
     * Carregar base e setar configuracoes.
     *
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
        $this->rest = new Rest();
    }

    /**
     * Executa via GET.
     *
     * @param $path
     * @param array $data
     * @param array $params
     *
     * @return bool|object|\SimpleXMLElement|string
     */
    public function get($path, array $data = [], array $params = [])
    {
        $ps = $this->getParams($params);
        $call = $this->rest->get($ps['url'] . $path, $data);

        return $this->toFormat($call, $ps['format']);
    }

    /**
     * Retornar pelo formato.
     *
     * @param Rest $call
     * @param $format
     *
     * @return bool|object|\SimpleXMLElement|string
     */
    protected function toFormat(Rest $call, $format)
    {
        switch ($format) {
            case 'json':
                return $call->json();
            case 'xml':
                return $call->xml();
            case 'html':
                return $call->html();
            default:
                return error('Formato %s nao implementado', $format);
        }
    }

    /**
     * Retorna oa parametros mesclados com os parametros padroes.
     *
     * @param array $params
     * @return array
     */
    protected function getParams(array $params)
    {
        $defaults = [];
        $defaults['format'] = Arr::get($this->configs, 'format_default', 'json');
        $defaults['url'] = Arr::get($this->configs, 'url');

        $lista = array_merge([], $defaults, $params);

        // Validar
        if (is_null($lista['url'])) {
            error('URL da api nao foi informada');
        }

        return $lista;
    }
}