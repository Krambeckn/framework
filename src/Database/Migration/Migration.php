<?php namespace NetForceWS\Database\Migration;

class Migration extends \Illuminate\Database\Migrations\Migration
{
    /**
     * Executar seed.
     *
     * @param $class
     */
    protected function seed($class)
    {
        \Artisan::call('db:seed', ['--class' => $class]);
    }

    /**
     * Executar migracao para atualizar versao.
     */
    public function up()
    {
        //...
    }

    /**
     * Executar migracao para desinstalar versao.
     */
    public function down()
    {
        //...
    }
}