<?php

namespace NetForceWS\Database;

class DatabaseServiceProvider extends \NetForceWS\Support\ServiceProvider
{
    protected $facades = [
        'Schema' => 'NetForceWS\Database\Facades\Schema',
    ];

}