<?php

namespace NetForceWS\Database\Models;

trait OrderByModel
{
    /**
     * Lista de campos padrao.
     *
     * @var array
     */
    protected $orderBy = [];

    /**
     * Retorna lista de ordenacao padrao do modelo.
     *
     * @return array
     */
    public function getOrders()
    {
        // Verificar se eh um array
        $this->orderBy = is_string($this->orderBy) ? explode(',', $this->orderBy) : $this->orderBy;

        $list = [];
        foreach ($this->orderBy as $order) {
            // Se for string
            if (is_string($order)) {
                $order = explode('.', $order);
                if (count($order) == 1) {
                    $order[] = 'asc';
                }
            }

            // Se for Array
            if (is_array($order)) {
                if (count($order) == 1) {
                    $order[] = 'asc';
                }
            }

            // Montar objeto de sort
            $sort = new \stdClass();
            $sort->column = $order[0];
            $sort->dir = $order[1];
            $list[] = $sort;
        }

        return $list;
    }
}