<?php

namespace NetForceWS\Database\Models\Search;

use NetForceWS\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SearchModel
{
    /**
     * @var Model
     */
    protected $model = null;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * Constructor.
     */
    public function __construct(Model $model, array $fields)
    {
        $this->model = $model;
        $this->fields = $fields;
    }

    /**
     * Aplicar estrutura de busca em uma BuilderQuery.
     *
     * @param Builder $query
     * @param array $groups
     */
    public function applyQuery(Builder $query, array $groups)
    {
        $query->where(function ($query) use ($groups) {
            foreach ($groups as $items) {
                $query->orWhere(function ($query) use ($items) {
                    foreach ($items as $item) {
                        $query->where($item->field, $item->op, $item->value, $item->link);
                    }
                });
            }
        });
    }

    /**
     * Montar estrutura de busca pelo $searchQuery.
     *
     * @param $searchQuery
     *
     * @return array
     */
    public function execute($searchQuery)
    {
        if (count($this->fields) == 0) {
            return [];
        }

        if ($searchQuery == '') {
            return [];
        }

        $searchQuery = Str::ascii($searchQuery);
        $tokens = $this->tokens($searchQuery);

        $list = [];
        foreach ($this->fields as $key => $field) {
            $type = $this->model->getAttrCast($key);
            $method = sprintf('field%s', Str::studly($type));
            $field = $this->prepareField($field);
            if (method_exists($this, $method)) {
                if (array_key_exists($key, $list) != true) {
                    $list[$key] = [];
                }
                call_user_func_array([$this, $method], [&$list[$key], $field, $tokens]);
            }
        }

        return $list;
    }

    /**
     * Tratar campos String.
     *
     * @param $list
     * @param $field
     * @param $tokens
     */
    protected function fieldString(&$list, $field, $tokens)
    {
        foreach ($tokens as $token) {
            $t = new \stdClass();
            $t->field = $field;
            $t->op = 'like';
            $t->value = '%' . $token->value . '%';
            $t->link = $token->link;

            $list[] = $t;
        }
    }

    /**
     * Tratar campos Float.
     *
     * @param $list
     * @param $field
     * @param $tokens
     */
    protected function fieldFloat(&$list, $field, $tokens)
    {
        $value = $tokens[0]->value;

        $t = new \stdClass();
        $t->field = $field;
        $t->op = '=';
        $t->value = $value;
        $t->link = 'or';

        $list[] = $t;
    }

    /**
     * Tratar campos integer.
     *
     * @param $list
     * @param $field
     * @param $tokens
     */
    protected function fieldInteger(&$list, $field, $tokens)
    {
        $this->fieldFloat($list, $field, $tokens);
    }

    /**
     * Montar lista de tokens.
     *
     * @param $query
     *
     * @return array
     */
    protected function tokens($query)
    {
        $ret = [];

        $link = 'and';

        preg_match_all('%([a-zA-Z0-9.-_\\\\/]+)%', $query, $tokens, PREG_PATTERN_ORDER);
        for ($i = 0; $i < count($tokens[0]); $i++) {
            $token = $tokens[0][$i];

            if (($token == 'ou') || ($token == 'e')) {
                $link = ($token == 'e') ? 'and' : 'or';
            } else {
                $t = new \stdClass();
                $t->value = $token;
                $t->link = $link;
                $ret[] = $t;
                $link = 'and';
            }
        }

        return $ret;
    }

    /**
     * Preparar campo.
     *
     * @param $field
     *
     * @return string
     */
    protected function prepareField($field)
    {
        return (strpos($field, '.') === false) ? sprintf('a.%s', $field) : $field;
    }
}