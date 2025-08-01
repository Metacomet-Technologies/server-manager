<?php

use Illuminate\Support\Facades\Route;
use Metacomet\ServerManager\Http\Controllers\Web\DashboardController;
use Metacomet\ServerManager\Http\Controllers\Web\ServerWebController;
use Metacomet\ServerManager\Http\Controllers\Web\SessionWebController;
use Metacomet\ServerManager\Http\Controllers\Web\TerminalController;
use Metacomet\ServerManager\Http\Middleware\SetInertiaRootView;

Route::middleware(array_merge(config('server-manager.web.middleware', ['web', 'auth']), [SetInertiaRootView::class]))
    ->prefix(config('server-manager.web.prefix', 'server-manager'))
    ->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('server-manager.dashboard');

        // Servers
        Route::get('/servers', [ServerWebController::class, 'index'])->name('server-manager.servers.index');
        Route::get('/servers/create', [ServerWebController::class, 'create'])->name('server-manager.servers.create');
        Route::post('/servers', [ServerWebController::class, 'store'])->name('server-manager.servers.store');
        Route::get('/servers/{server}', [ServerWebController::class, 'show'])->name('server-manager.servers.show');
        Route::get('/servers/{server}/edit', [ServerWebController::class, 'edit'])->name('server-manager.servers.edit');
        Route::put('/servers/{server}', [ServerWebController::class, 'update'])->name('server-manager.servers.update');
        Route::delete('/servers/{server}', [ServerWebController::class, 'destroy'])->name('server-manager.servers.destroy');
        Route::post('/servers/{server}/test-connection', [ServerWebController::class, 'testConnection'])->name('server-manager.servers.test-connection');

        // Sessions
        Route::get('/sessions', [SessionWebController::class, 'index'])->name('server-manager.sessions.index');
        Route::get('/sessions/create', [SessionWebController::class, 'create'])->name('server-manager.sessions.create');
        Route::post('/sessions', [SessionWebController::class, 'store'])->name('server-manager.sessions.store');
        Route::delete('/sessions/{session}', [SessionWebController::class, 'destroy'])->name('server-manager.sessions.destroy');
        Route::get('/sessions/{session}/share', [SessionWebController::class, 'share'])->name('server-manager.sessions.share');
        Route::post('/sessions/{session}/share', [SessionWebController::class, 'storeShare'])->name('server-manager.sessions.share.store');
        Route::delete('/sessions/{session}/share/{user}', [SessionWebController::class, 'destroyShare'])->name('server-manager.sessions.share.destroy');

        // Terminal
        Route::get('/sessions/{session}/terminal', [TerminalController::class, 'show'])->name('server-manager.terminal');
    });
