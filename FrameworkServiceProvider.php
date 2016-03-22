<?php namespace NetForceWS\Framework;

class FrameworkServiceProvider extends \Illuminate\Support\AggregateServiceProvider
{
    protected $providers = [
        'NetForceWS\Database\DatabaseServiceProvider',
        'NetForceWS\IO\IoServiceProvider',

        //'NetForce\Formatter\FormatterServiceProvider',
        //'NetForce\Compiler\CompilerServiceProvider',
        //'NetForce\Configs\ConfigsServiceProvider',
        //'NetForce\Workflow\WorkflowServiceProvider',
    ];
}