<?php

namespace Metacomet\ServerManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Metacomet\ServerManager\ServerManager
 */
class ServerManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Metacomet\ServerManager\ServerManager::class;
    }
}
