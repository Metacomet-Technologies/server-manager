<?php

namespace Metacomet\ServerManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SetInertiaRootView
{
    public function handle(Request $request, Closure $next)
    {
        Inertia::setRootView('server-manager::app');
        
        return $next($request);
    }
}