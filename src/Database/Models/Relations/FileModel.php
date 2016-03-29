<?php

namespace NetForceWS\Database\Models\Relations;

use \File;
use \Storage;

class FileModel
{
    const extension = 'file';

    protected $model = null;
    protected $attribute = '';
    protected $realPath = '';
    protected $name = '';
    protected $loaded = false;

    public function __construct($model, $attribute)
    {
        $this->model = $model;
        $this->attribute = $attribute;
    }

    /**
     * Associar informação do arquivo que já esta no storage.
     *
     * @param $originalName
     *
     * @return bool
     */
    public function associate($originalName)
    {
        $id_name = sprintf('%s.%s.%s', $this->attribute, $this->model->id, self::extension);
        $file = File::combine(get_class($this->model), $id_name);

        $this->realPath = $file;
        $this->name = $originalName;
        $this->loaded = true;

        return true;
    }

    /**
     * Enviar arquivo para o storage.
     *
     * @param $path
     * @param bool $originalName
     *
     * @return bool
     */
    public function attach($path, $originalName = false)
    {
        $originalName = ($originalName === false) ? File::fileName($path) : $originalName;
        $id_name = sprintf('%s.%s.%s', $this->attribute, $this->model->id, self::extension);
        $file = File::combine(get_class($this->model), $id_name);

        if (Storage::drive()->put($file, File::get($path))) {
            return $this->associate($originalName);
        }

        return false;
    }

    /**
     * Enviar arquivo para o storage no save do model.
     *
     * @param $path
     * @param bool $originalName
     */
    public function attachInSave($path, $originalName = false)
    {
        $name = $this->attribute;

        $this->model->saving(function ($obj) use ($name, $path, $originalName) {
            $originalName = ($originalName == false) ? File::fileName($path) : $originalName;
            $obj->{$name} = $originalName;
        });

        $this->model->saved(function ($obj) use ($name, $path, $originalName) {
            $file = $obj->{$name};
            $file->attach($path, $originalName);
        });
    }

    /**
     * Enviar content para o storage no save do model.
     *
     * @param $content
     * @param $originalName
     */
    public function setInSave($content, $originalName)
    {
        $name = $this->attribute;

        $this->model->saving(function ($obj) use ($name, $originalName) {
            $obj->{$name} = $originalName;
        });

        $this->model->saved(function ($obj) use ($name, $content, $originalName) {
            $file = $obj->{$name};
            $file->associate($originalName);
            $file->set($content);
        });
    }

    /**
     * Nome do arquivo original.
     *
     * @return string
     */
    public function fileName()
    {
        return $this->name;
    }

    /**
     * Retorna o conteudo do arquivo do storage.
     *
     * @return bool|string
     */
    public function get()
    {
        if (!$this->loaded) {
            return false;
        }

        $code = Storage::drive()->get($this->realPath);

        return $code;
    }

    /**
     * Enviar um novo conteudo.
     *
     * @param $content
     *
     * @return bool
     */
    public function set($content)
    {
        if (!$this->loaded) {
            return false;
        }

        return Storage::drive()->put($this->realPath, $content);
    }

    /**
     * Tamnho do arquivo no storage.
     *
     * @return bool|int
     */
    public function size()
    {
        if (!$this->loaded) {
            return false;
        }

        return Storage::drive()->size($this->realPath);
    }

    /**
     * Data da ultima modificação.
     *
     * @return bool|int
     */
    public function lastModified()
    {
        if (!$this->loaded) {
            return false;
        }

        return Storage::drive()->lastModified($this->realPath);
    }

    /**
     * Se o arquivo existe no storage.
     *
     * @return bool
     */
    public function exists()
    {
        if (!$this->loaded) {
            return false;
        }

        return Storage::drive()->exists($this->realPath);
    }

    /**
     * Extensão do arquivo.
     *
     * @return bool|string
     */
    public function extension()
    {
        if (!$this->loaded) {
            return false;
        }

        return File::extension($this->name);
    }

    /**
     * Excluir arquivo no storage.
     *
     * @return bool
     */
    public function delete()
    {
        if (!$this->loaded) {
            return false;
        }

        $this->model->{$this->attribute} = null;
        if (Storage::drive()->exists($this->realPath)) {
            return Storage::drive()->delete($this->realPath);
        }

        return true;
    }

    /**
     * Retorno o conteudo do arquivo em formato Image Base64.
     *
     * @return bool|string
     */
    public function toImage64($noimg = false)
    {
        $code = false;

        // Carregar arquivo no storage
        if ($this->loaded && $this->exists()) {
            $code = base64_encode($this->get());
        }

        // Verificar se deve carregar noImage
        if (($code === false) && ($noimg !== false)) {
            if (File::exists($noimg)) {
                $code = base64_encode(File::get($noimg));
            }
        }

        if ($code === false) {
            return false;
        }

        return sprintf('data:image/%s;base64,%s', $this->extension(), $code);
    }

    public static function image64($path)
    {
        if (File::exists($path) != true) {
            return false;
        }

        $code = base64_encode(File::get($path));

        return sprintf('data:image/%s;base64,%s', File::extension($path), $code);
    }

    /**
     * Retorna se foi informado algum arquivo.
     *
     * @return bool
     */
    public function loaded()
    {
        return $this->loaded;
    }
}