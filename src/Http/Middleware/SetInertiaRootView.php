<?php

namespace Metacomet\ServerManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetInertiaRootView
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (class_exists('\Inertia\Inertia')) {
            \Inertia\Inertia::setRootView('server-manager::app');
        }

        return $next($request);
    }
}
