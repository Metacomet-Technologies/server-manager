<?php

namespace Metacomet\ServerManager\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Inertia\Inertia;

abstract class BaseWebController extends Controller
{
    public function __construct()
    {
        Inertia::setRootView('server-manager::app');
    }
}
