<?php namespace NetForceWS\Validation;

class ValidationServiceProvider extends \NetForceWS\Support\ServiceProvider
{
    /**
     * Boot do Provider.
     */
    public function boot()
    {
        parent::boot();

        $trans = app('translator');

        \Validator::extend('id', '\NetForceWS\Validation\Validators@id', $trans->get('O :attribute deve ser um ID (Ex.: ABC999...).'));
        \Validator::extend('route', '\NetForceWS\Validation\Validators@route', $trans->get('O :attribute não é uma rota válida.'));
        \Validator::extend('domain', '\NetForceWS\Validation\Validators@domain', $trans->get('O :attribute esta inválido.'));
        \Validator::extend('checkpass', '\NetForceWS\Validation\Validators@checkpass', $trans->get('A senha antiga não confere.'));
    }
}