<?php namespace NetForceWS\Http;

use NetForceWS\Http\Utils\IndexReturn;
use NetForceWS\Support\Str;
use \Route;

trait ApiRegisterController
{
    use ResourceController;
    use Error;

    public function apiIndex()
    {
        try {
            if ($this->request->get('view') == 'association') {
                return $this->apiAssociation();
            }

            $return = $this->index();

            if ($return instanceof IndexReturn) {
                return $this->index()->toReponse();
            }

            return \Response::json($return);
        } catch (\Exception $e) {
            return $this->error($e, true);
        }
    }

    protected function apiAssociation()
    {
        // Objeto de retorno
        $return = new \stdClass();
        $return->items = [];
        $return->count = 0;

        // Verificar se foi informado o search
        $search = trim($this->request->input('q', ''));
        if ($search == '') {
            return \Response::json($return);
        }

        $info = $this->index();

        // Preparar retorno
        foreach ($info->data() as $item) {
            $i = new \stdClass();
            $i->id = $item->id;
            $i->text = $item->getLabel();
            $return->items[] = $i;
        }
        $return->count = count($return->items);

        return \Response::json($return);
    }

    public function apiCreate()
    {
        try {
            return \Response::json($this->create());
        } catch (\Exception $e) {
            return $this->error($e, true);
        }
    }

    public function apiShow()
    {
        try {
            return \Response::json($this->show());
        } catch (\Exception $e) {
            return $this->error($e, true);
        }
    }

    public function apiStore()
    {
        try {
            return \Response::json($this->store());
        } catch (\Exception $e) {
            return $this->error($e, true);
        }
    }

    public function apiUpdate()
    {
        try {
            return \Response::json($this->update());
        } catch (\Exception $e) {
            return $this->error($e, true);
        }
    }

    public function apiCommand()
    {
        try {
            return \Response::json($this->command());
        } catch (\Exception $e) {
            return $this->error($e, true);
        }
    }

    public function apiDelete()
    {
        try {
            return \Response::json($this->delete());
        } catch (\Exception $e) {
            return $this->error($e, true);
        }
    }

    /**
     * Registrar metodos do modelo.
     *
     * @param $uri
     * @param array $options
     * @param string $breadcrumbs
     */
    public static function register($uri, array $options = [])
    {
        $controller = '\\' . get_called_class();
        $id_name = Str::last($uri) . '_id';
        $methods = ['index', 'create', 'show', 'store', 'update', 'command', 'delete'];
        $methods = self::getResourceMethods($methods, $options);

        // Index
        if (in_array('index', $methods)) {
            Route::get($uri, $controller . '@apiIndex');
        }
        // Create
        if (in_array('create', $methods)) {
            Route::get($uri . '/create', $controller . '@apiCreate');
        }
        // Show
        if (in_array('show', $methods)) {
            Route::get($uri . '/{' . $id_name . '}', $controller . '@apiShow')->where([$id_name => '[0-9]+']);
        }

        // Store
        if (in_array('store', $methods)) {
            Route::post($uri, $controller . '@apiStore');
        }
        // Update
        if (in_array('update', $methods)) {
            Route::post($uri . '/{' . $id_name . '}', $controller . '@apiUpdate')->where([$id_name => '[0-9]+']);
        }
        // Command
        if (in_array('command', $methods)) {
            Route::post($uri . '/{' . $id_name . '}/{command}', $controller . '@apiCommand')->where([$id_name => '[0-9]+']);
        }
        // Delete
        if (in_array('delete', $methods)) {
            Route::delete($uri . '/{ids}', $controller . '@apiDelete');
        }
    }

    /**
     * Tratar lista de methods.
     *
     * @param $defaults
     * @param $options
     *
     * @return array
     */
    protected static function getResourceMethods($defaults, $options)
    {
        if (isset($options['only'])) {
            return array_intersect($defaults, (array) $options['only']);
        } elseif (isset($options['except'])) {
            return array_diff($defaults, (array) $options['except']);
        }

        return $defaults;
    }
}