<?php namespace NetForceWS\Http;

use NetForceWS\Http\Services\ServiceModel;
use NetForceWS\Http\Utils\IndexReturn;
use NetForceWS\Http\Utils\RouteUtils;
use NetForceWS\Support\Str;
use NetForceWS\Database\Models\Search\SearchModel;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use \Route;
use \DB;

trait ResourceController
{
    /**
     * @var Request
     */
    protected $request = null;

    /**
     * @var Model
     */
    public $model = '';

    /**
     * Lista de campos para ordenacao padrao, quando nao fo informando o order.
     * @var string
     */
    public $defaultSort = '';

    /**
     * Campos utilizados para o search.
     * @var array
     */
    public $searches = [];

    /**
     * Lista de campos de referencia com a rota.
     * @var array
     */
    public $references = [];

    /**
     * Lista de filtros de contexto.
     * @var array
     */
    protected $wheres = [];

    /**
     * @var bool
     */
    public $validatesStore = false;

    /**
     * @var bool
     */
    public $validatesUpdate = false;

    /**
     * @var string
     */
    public $messageModelNotFound = 'Registro não foi encontrado';

    /**
     * Preparar estrutura.
     */
    public function prepare()
    {
        // Carregar modelo
        $this->model = app($this->model);

        // Carregar request
        $this->request = app('request');
    }

    /**
     * Carregar lista.
     */
    public function getList()
    {
        $context = new \NetForceWS\Http\Context\Context();

        // Preparar queries
        $qcount = $this->model->newQuery()->select(DB::raw('count(*) recs'));
        $query = $this->model->newQuery();

        // Atribuir alias da tabela principal com A
        /*
        $q       = $query->getQuery();
        $q->from = sprintf('%s as a', $q->from);
        $query->setQuery($q);
        $q       = $qcount->getQuery();
        $q->from = sprintf('%s as a', $q->from);
        $qcount->setQuery($q);
        /**/

        // Filtros de contexto
        $this->applyWhereContext($query);
        $this->applyWhereContext($qcount);

        // Filtros (Query e Count)
        if ($context->searchQuery != '') {
            $search = new SearchModel($this->model, $this->searches);
            $items = $search->execute($context->searchQuery);
            $search->applyQuery($query, $items);
            $search->applyQuery($qcount, $items);
        }

        // Paginação
        $query->forPage($context->page, $context->pageSize);

        // Ordenação
        $context->defaultSort = $this->defaultSort;
        foreach ($context->sorts as $sort) {
            $query->orderBy($sort->column, $sort->dir);
        }

        // Carregar o número de registros do contexto
        $context->setCount(intval($qcount->first('recs')->recs));

        // Retornar
        return new IndexReturn($this->request, $query, $context);
    }

    /**
     * GET / Listar.
     */
    public function index()
    {
        return $this->getList();
    }

    /**
     * Form Create.
     */
    public function create()
    {
        return $this->model;
    }

    /**
     * Form Exibir.
     */
    public function show()
    {
        $id = $this->getRouteId();
        return $this->getModel($id);
    }

    /**
     * POST: Store|Create /.
     */
    public function store()
    {
        return DB::transaction(function () {

            // Criar serviço
            $service = $this->getService();

            // Carregar Regras
            $rules = ($this->validatesStore === false) ? $this->validatesUpdate : $this->validatesStore;
            $rules = (is_array($rules) ? $rules : $this->model->validates);
            $params = ['table' => $this->model->getTable()];
            $service->validate($this->request->all(), $rules, $params, $this->references);

            // Executar
            $return = $service->store($this->model, $this->request, $this->references);

            return $return;
        });
    }

    /**
     * POST: Update|Edit /{id}.
     */
    public function update()
    {
        return DB::transaction(function () {

            // Carregar ID
            $id = $this->getRouteId();

            // Carregar model
            $this->model = $this->model->find($id);

            // Criar serviço
            $service = $this->getService();

            // Validar
            $data = array_merge([], $this->model->getAttributes(), $this->request->all());
            $rules = $this->validatesUpdate;
            $rules = (is_array($rules) ? $rules : $this->model->validates);
            $params = ['table' => $this->model->getTable(), 'id' => $id];
            $service->validate($data, $rules, $params, $this->references);

            // Executar
            $return = $service->update($this->model, $this->request, $this->references);

            return $return;
        });
    }

    /**
     * POST: Command /{id}/{comand}.
     */
    public function command()
    {
        return DB::transaction(function () {

            // Carregar parametros
            $id = $this->getRouteId();
            $command = Route::input('command');

            // Carregar model
            $this->model = $this->getModel($id);

            $method = 'command' . Str::studly($command);
            if (method_exists($this, $method) != true) {
                error('Comando %s não foi implementado', $command);
            }

            return call_user_func_array([$this, $method], [$id]);
        });
    }

    /**
     * DELETE: Excluir /{ids}.
     */
    public function delete()
    {
        return DB::transaction(function () {

            // Carregar IDs
            $ids = explode(',', Route::input('ids'));

            // Criar serviço
            $service = $this->getService();

            foreach ($ids as $id) {
                // Carregar model
                $model = $this->model->find($id);
                if ($model !== null) {
                    // Executar
                    $service->delete($model, $this->request);
                }
            }

            return true;
        });
    }

    /**
     * Carregar um registro.
     *
     * @param $id
     *
     * @return mixed
     */
    public function getModel($id)
    {
        // Carregar query
        $query = $this->model->where('id', $id);

        // Filtro de contexto
        $this->applyWhereContext($query);

        // Carregar model
        $ret = $query->first();

        //$ret = $this->model->find($id);
        if (is_null($ret)) {
            error($this->messageModelNotFound);
        }

        $this->model = $ret;

        return $ret;
    }

    /**
     * Aplicar na Query os filtros de contexto.
     *
     * @param $query
     */
    protected function applyWhereContext($query)
    {
        // Filtros de contexto
        if (count($this->wheres) > 0) {
            $query->where($this->wheres);
        }

        // Filtros de referencia
        foreach ($this->references as $ref_campo => $ref_name) {
            $id = Route::input($ref_name);
            if ($id !== null) {
                $query->where($ref_campo, $id);
            }
        }
    }

    /**
     * Retorna o ID pela rota.
     *
     * @return integer
     */
    public function getRouteId()
    {
        return RouteUtils::getRouteId();
    }

    /**
     * @return ServiceModel
     */
    public function getService()
    {
        return new ServiceModel();
    }

    /**
     * Adicona um where de contexto.
     *
     * @param $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     *
     * @return $this
     */
    protected function where($column, $operator = null, $value = null)
    {
        if (func_num_args() == 2) {
            list($value, $operator) = [$operator, '='];
        }

        $this->wheres[] = compact('column', 'operator', 'value');

        return $this;
    }
}