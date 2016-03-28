<?php namespace NetForceWS\Database\Models;

use NetForceWS\Database\Models\Relations\FileModel;

trait CastModel
{
    /**
     * Retorna o tipo de campo
     * @param $key
     * @return string
     */
    public function getAttrCast($key)
    {
        if (array_key_exists($key, $this->casts))
            return $this->getCastType($key);
        return 'string';
    }

    /**
     * @return array
     */
    public function getCasts()
    {
        return $this->casts;
    }

    protected function castAttribute($key, $value)
    {
        switch ($this->getCastType($key))
        {
            case 'file':
                $file = new FileModel($this, $key);
                if (is_null($value) != true)
                    $file->associate($value);
                return $file;

            default:
                return parent::castAttribute($key, $value);
        }
    }
}