<?php namespace NetForceWS\Database\Models;

trait LabelModel
{
    public $fieldLabel = '';

    /**
     * Retorna o campo idenficação do modelo.
     *
     * @return mixed|string
     */
    public function getLabel()
    {
        // Verificar string
        if ((is_string($this->fieldLabel)) && ($this->fieldLabel == '')) {
            return '';
        }

        // Tratar em array
        $fields = is_array($this->fieldLabel) ? $this->fieldLabel : explode(',', $this->fieldLabel);
        if (count($fields) == 0) {
            return '';
        }

        // Carregar valores
        $values = [];
        foreach ($fields as $field) {
            $values[] = $this->{$field};
        }

        return implode(' - ', $values);
    }
}