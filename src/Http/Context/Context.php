<?php namespace NetForceWS\Http\Context;

use Illuminate\Http\Request;
use NetForceWS\Support\Properties;
use NetForceWS\IO\Filesystem;

class Context
{
    use Properties;

    /**
     * @var Request
     */
    protected $request = null;

    /**
     * @var Filesystem
     */
    protected $files = null;

    /**
     * @var string
     */
    public $defaultSort = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->request = app('request');
        $this->files   = app('files');
    }

    /**
     * URL base
     * @return string
     */
    public function getUriAttribute()
    {
        return $this->request->url();
    }

    /**
     * ID para user ne sessao e cookie
     * @return string
     */
    public function getIdAttribute()
    {
        return sprintf('model_%s', md5($this->uri));
    }

    /**
     * Numero de registros
     * @return int
     */
    public function getCountAttribute()
    {
        return array_key_exists('count', $this->properties) ? $this->properties['count'] : 1;
    }

    /**
     * Informar o numero de registros
     * @param $count
     */
    public function setCount($count)
    {
        $this->properties['count'] = $count;
        unset($this->properties['pages']);
        unset($this->properties['pageActive']);
        unset($this->properties['pageBack']);
        unset($this->properties['pageNext']);
    }


    /**
     * Paginas conforma o numero de registros e o pageSize
     * @return array
     */
    public function getPagesAttribute()
    {
        $pages = [];

        $max_pages  = intval(config('view.grid.max-pages', 11));
        $num_pages  = floor($this->count / $this->pageSize);
        $num_pages += ((($this->count % $this->pageSize) != 0) ? 1 : 0);

        if (($num_pages <= 1) || ($this->count <= $this->pageSize))
            return [];

        $meio      = intval($max_pages / 2);
        $num_pages = (($num_pages > $max_pages) ? $max_pages : $num_pages);

        $ini          = (($this->page <= $meio) ? 1 : ($this->page - $meio) + 1);
        $count        = ($ini + $num_pages - 1);

        for ($i = $ini; $i <= $count; $i++)
        {
            $p         = new \stdClass();
            $p->label  = $i;
            $p->index  = count($pages);
            $p->active = ($this->page == $i);
            $p->href   = $this->url('', array('page' => $i));
            $pages[]   = $p;
        }

        return $pages;
    }

    /**
     * Página selecionada
     * @return int|false
     */
    public function getPageActiveAttribute()
    {
        $active_index = false;
        foreach ($this->pages as $p)
            $active_index = ($p->active ? $p->index : $active_index);
        return $active_index;
    }

    /**
     * Pagina anterior
     * @return int|false
     */
    public function getPageBackAttribute()
    {
        return ($this->pageActive > 0) ? $this->pages[$this->pageActive - 1] : false;
    }

    /**
     * proxima pagina
     * @return int|false
     */
    public function getPageNextAttribute()
    {
        if (count($this->pages) == 0)
            return false;
        return ($this->pageActive < count($this->pages) - 1) ? $this->pages[$this->pageActive + 1] : false;
    }

    /**
     * Qtdade de registros por pagina
     * @return array
     */
    public function getPageSizesAttribute()
    {
        $sizes     = [];
        $sizes[15] = $this->url('', ['page' => 1, 'page_size' => 15]);
        $sizes[30] = $this->url('', ['page' => 1, 'page_size' => 30]);
        $sizes[60] = $this->url('', ['page' => 1, 'page_size' => 60]);

        return $sizes;
    }

    /**
     * Pagina atual
     * @return int
     */
    public function getPageAttribute()
    {
        return intval($this->request->get('page', 1));
    }

    /**
     * Retorna o pageSize
     * @return int
     */
    public function getPageSizeAttribute()
    {
        $page_id   = sprintf('%s_page_size', $this->id);
        $page_size = intval(\Cookie::get($page_id, 15));
        $page_size = intval($this->request->get('page_size', $page_size));

        // Guardar no cookie a última alteração do page_size (lembrar alteração do usuário)
        if ($this->request->has('page_size'))
            \Cookie::queue($page_id, $this->request->get('page_size'));

        return $page_size;
    }

    /**
     * Retorna Ordenacao
     * @return string
     */
    public function getSortAttribute()
    {
        $sort_id = sprintf('%s_sort', $this->id);
        $sort    = \Cookie::get($sort_id, $this->defaultSort);
        $sort    = $this->request->get('sort', $sort);

        // Guardar no cookie a última alteração de ordenação (lembrar alteração do usuário)
        if ($this->request->has('sort'))
            \Cookie::queue($sort_id, $this->request->get('sort'));

        return $sort;
    }

    /**
     * Retorna a lista de campos da ordenação
     * @return array
     */
    public function getSortsAttribute()
    {
        $sorts = explode(',', trim($this->sort));

        $list = [];
        if ($sorts[0] != '')
        {
            foreach ($sorts as $i => $sort)
            {
                $info = explode('.', $sort);
                if (count($info) == 1)
                    $info[] = 'asc';

                $s          = new \stdClass();
                $s->index   = $i;
                $s->column  = trim($info[0]);
                $s->dir     = ((strtolower(trim($info[1])) == 'desc') ? 'desc' : 'asc');

                $list[$s->column] = $s;
            }
        }

        return $list;
    }

    /**
     * Query da busca
     * @return string
     */
    public function getSearchQueryAttribute()
    {
        return $this->request->get('q', '');
    }

    /**
     * URL da busca
     * @return string
     */
    public function getSearchUrlAttribute()
    {
        return $this->url('', array('page' => 1, 'page_size' => $this->pageSize));
    }

    /**
     * Montar URL
     */
    protected function url($part, $params = false, $extend = true)
    {
        $params = (($params === false) ? array() : $params);
        $params = ($extend ? array_merge($this->request->all(), $params) : $params);

        $url    = (($part != '') ? $this->files->combine($this->uri, $part) : $this->uri);

        // Parâmetros
        foreach ($params as $n => $v)
            $url .= sprintf('%s%s=%s', ((strpos($url, '?') === false) ? '?' : '&'), $n, urlencode($v));

        return $url;
    }
}