<?php namespace NetForceWS\Http\Utils;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use NetForceWS\Http\Context\Context;

class IndexReturn
{
    /**
     * @var QueryException
     */
    public $query = null;

    /**
     * @var Context
     */
    public $context = null;

    /**
     * @var array
     */
    protected $data = null;

    /**
     * @var Request
     */
    protected $request = null;

    /**
     * @param $data
     * @param $context
     */
    public function __construct(Request $request, $query, $context)
    {
        $this->request = $request;
        $this->query   = $query;
        $this->context = $context;
    }

    /**
     * Retorna a DATA pela Query
     * @return array
     */
    public function data()
    {
        if (is_null($this->data) != true)
            return $this->data;

        return $this->data = $this->query->get();
    }

    /**
     * Retorna para o Response
     * @return \Illuminate\Http\JsonResponse
     */
    public function toReponse()
    {
        // Com contexto
        if ($this->request->get('view') == 'full')
        {
            return \Response::json($this->toObject());
        }

        // Retornar somente dados
        return \Response::json($this->data());
    }

    /**
     * Retorna em formato de objeto
     * @return \stdClass
     */
    public function toObject()
    {
        $ret          = new \stdClass();
        $ret->data    = $this->data();
        $ret->context = $this->context;

        return $ret;
    }
}