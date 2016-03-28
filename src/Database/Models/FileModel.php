<?php

namespace NetForceWS\Database\Models;

trait FileModel
{
    /**
     * Registrar eventos.
     */
    public static function bootFileModel()
    {
        // Excluir arquivos
        self::deleting(function ($model) {
            $casts = $model->getCasts();
            foreach ($casts as $attr => $type) {
                if ($type == 'file') {
                    $file = $model->{$attr};
                    if (is_null($file) != true) {
                        $file->delete();
                    }
                }
            }

        }, 0);
    }

    /**
     * getAttribute.
     *
     * @param $key
     *
     * @return FileModel
     */
    public function getAttribute($key)
    {
        // verificar se deve autocarregar FileModel quando o attribute não foi informado
        // Isso é não não retornar null quando não tem os attributos
        if (!(array_key_exists($key, $this->attributes) || $this->hasGetMutator($key))) {
            if (array_key_exists($key, $this->casts) && ($this->casts[$key] == 'file')) {
                return new \NetForce\Models\Relations\FileModel($this, $key);
            }
        }

        return parent::getAttribute($key);
    }
}