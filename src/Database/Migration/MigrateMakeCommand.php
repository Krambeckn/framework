<?php

namespace NetForceWS\Database\Migration;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MigrateMakeCommand extends \Illuminate\Database\Console\Migrations\MigrateMakeCommand
{
    protected $signature = 'make:migration {name : The name of the migration.}
        {--create= : The table to be created.}
        {--table= : The table to migrate.}
        {--path= : The location where the migration file should be created.}
        {--no-prefix : Ignore prefix "Migration" in the name.}';

    protected function writeMigration($name, $table, $create)
    {
        $with_prefix = ($this->input->getOption('no-prefix') === false);
        $name = $with_prefix ? sprintf('migration_%s', $name) : $name;
        $path = $this->getMigrationPath();
        $target = $this->makeName($name, $path);
        $class = studly_case($name);

        $t = new \NetForceWS\IO\Template();
        $t->file($this->getStub($table, $create), $target);
        $t->param('class', $class);
        $t->param('table', $table);

        $file = pathinfo($target, PATHINFO_FILENAME);

        $this->line("<info>Created Migration:</info> $file");

        return true;
    }

    protected function getStub($table, $create)
    {
        if (is_null($table)) {
            return \File::combine($this->getStubPath(), 'blank.php.txt');
        }

        if ($create) {
            return \File::combine($this->getStubPath(), 'create.php.txt');
        }

        return \File::combine($this->getStubPath(), 'update.php.txt');
    }

    public function getStubPath()
    {
        return __DIR__ . '/Templates';
    }

    protected function makeName($name, $path)
    {
        $date = date('Y_m_d_His');

        return sprintf('%s/%s_%s.php', $path, $date, $name);
    }

}