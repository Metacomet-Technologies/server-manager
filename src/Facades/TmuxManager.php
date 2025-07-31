<?php

namespace Metacomet\TmuxManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Metacomet\TmuxManager\TmuxManager
 */
class TmuxManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Metacomet\TmuxManager\TmuxManager::class;
    }
}
