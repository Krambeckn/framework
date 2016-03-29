<?php namespace NetForceWS\Http\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use NetForceWS\Support\ExceptionAttributes;
use NetForceWS\Support\Num;
use NetForceWS\Support\Str;
use NetForceWS\Validation\Validators;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use \Hash;

class ServiceModel
{
    /**
     * Campos a ignorar.
     * @var array
     */
    protected $applyIgnores = [
        '*_confirmation',
    ];

    /**
     * Comandos a executar depois do save.
     * @var array
     */
    protected $after = [];

    public function validate(array $data, array $rules, array $parans, array $references)
    {
        // Traduzir parametros das regras
        $data = array_merge([], $data, $parans);

        // Tratar referencia
        $data = $this->loadReferences($data, $references);

        // Tratar regras
        $rules = Validators::translateParams($data, $rules);

        // Validar
        $validator = app('validator')->make($data, $rules);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $items = $messages->toArray();

            throw new ExceptionAttributes(app('translator')->get('Error validating fields.'), 0, $items);
        }
    }

    /**
     * Create.
     *
     * @param $model
     * @param Request $request
     * @param array $references
     *
     * @return Model
     */
    public function store($model, Request $request, array $references, $ignoreApply = false)
    {
        // Aplicar campos
        if ($ignoreApply != true) {
            $this->apply($model, $request, $references);
        }

        // Salvar
        $model->save();

        // After
        foreach ($this->after as $cmd) {
            $cmd($model);
        }

        // Retornar modelo salvo
        return $model;
    }

    /**
     * Update.
     *
     * @param $model
     * @param Request $request
     * @param array $references
     *
     * @return Model
     */
    public function update($model, Request $request, array $references, $ignoreApply = false)
    {
        // Aplicar campos
        if ($ignoreApply != true) {
            $this->apply($model, $request, $references);
        }

        // Salvar
        $model->save();

        // After
        foreach ($this->after as $cmd) {
            $cmd($model);
        }

        // Retornar modelo salvo
        return $model;
    }

    /**
     * Delete.
     *
     * @param Model $model
     * @param Request $request
     *
     * @return bool
     */
    public function delete(Model $model, Request $request)
    {
        // Verificar "multassociations" para zerar
        foreach ($model->getCasts() as $key => $type) {
            if ($type == 'multassociations')
                $model->$key()->sync([], true);
        }

        // Excluir
        $model->delete();

        // Retornar ok
        return true;
    }

    /**
     * Carregar campos do request para o modelo.
     *
     * @param $model
     * @param Request $request
     * @param array $references
     */
    public function apply($model, Request $request, array $references)
    {
        // Carregar valores informados
        $all = $request->all();

        // Verificar referencias
        $all = $this->loadReferences($all, $references);

        // Aplcar campos
        foreach ($all as $name => $value) {
            if ($this->hasApply($name)) {
                // Verificar se tem que tratar aplicação
                $type = $model->getAttrCast($name);
                $method = sprintf('apply%s', Str::studly($type));
                if (method_exists($this, $method)) {
                    call_user_func_array([$this, $method], [$model, $name, $value, $request]);
                } else {
                    $this->applyString($model, $name, $value, $request);
                }
            }
        }

        // Verificar "multassociations" não informados
        foreach ($model->getCasts() as $key => $type) {
            if (($type == 'multassociations') && (array_key_exists($key, $all) != true)) {
                $this->applyMultassociations($model, $key, [], $request);
            }
        }
    }

    /**
     * Verificar se um campo deve ser ignorado na hora de aplicar do request no model.
     *
     * @param $name
     *
     * @return bool
     */
    protected function hasApply($name)
    {
        foreach ($this->applyIgnores as $pattern) {
            if (Str::is($pattern, $name)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Tratar string.
     *
     * @param $model
     * @param $name
     * @param $value
     * @param Request $request
     */
    protected function applyString($model, $name, $value, Request $request)
    {
        $model->{$name} = $value;
    }

    /**
     * Tratar Boolean.
     *
     * @param $model
     * @param $name
     * @param $value
     * @param Request $request
     */
    protected function applyBoolean($model, $name, $value, Request $request)
    {
        $model->{$name} = (($value == 1) || ($value == true) || (strtolower($value) == 'true'));
    }

    /**
     * Tratar password.
     *
     * @param $model
     * @param $name
     * @param $value
     * @param Request $request
     */
    protected function applyPassword($model, $name, $value, Request $request)
    {
        $this->applyString($model, $name, Hash::make($value), $request);
    }

    /**
     * Tratar numeros.
     *
     * @param $model
     * @param $name
     * @param $value
     * @param Request $request
     */
    protected function applyFloat($model, $name, $value, Request $request)
    {
        $model->{$name} = Num::value($value);
    }

    /**
     * Tratar inteiros.
     *
     * @param $model
     * @param $name
     * @param $value
     * @param Request $request
     */
    protected function applyInteger($model, $name, $value, Request $request)
    {
        $model->{$name} = intval($value);
    }

    /**
     * Tratar Arquivos.
     *
     * @param $model
     * @param $name
     * @param $value
     * @param Request $request
     */
    protected function applyFile($model, $name, $value, Request $request)
    {
        $model->saving(function ($obj) use ($name, $value, $request) {
            if (($value instanceof UploadedFile) && $request->hasFile($name)) {
                $obj->{$name} = $value->getClientOriginalName();
            } else if (is_string($value)) {
                $obj->{$name} = $value;
            }
        });

        $model->saved(function ($obj) use ($name, $value, $request) {
            $file = $obj->{$name};
            if (($value instanceof UploadedFile) && $request->hasFile($name)) {
                $file->attach($value->getRealPath(), $value->getClientOriginalName());
            } else {
                if ($value == '') {
                    $file->delete();
                }
            }
        });
    }

    /**
     * Tratar mult associacoes.
     *
     * @param $model
     * @param $name
     * @param $value
     * @param Request $request
     */
    protected function applyMultassociations($model, $name, $value, Request $request)
    {
        $value = (is_array($value) != true) ? [] : $value;

        $this->after[] = function (Model $model) use ($name, $value) {
            $model->$name()->sync($value, true);
        };
    }

    /**
     * Aplicar referencias.
     *
     * @param array $values
     * @param array $references
     *
     * @return array
     */
    protected function loadReferences(array $values, array $references)
    {
        // Verificar referencias
        foreach ($references as $ref_campo => $ref_name) {
            $values[$ref_campo] = array_key_exists($ref_campo, $values) ? $values[$ref_campo] : \Route::input($ref_name);
        }

        return $values;
    }
}