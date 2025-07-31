<?php

use Illuminate\Support\Facades\Route;
use Metacomet\ServerManager\Http\Controllers\CommandController;
use Metacomet\ServerManager\Http\Controllers\ServerController;
use Metacomet\ServerManager\Http\Controllers\SessionController;
use Metacomet\ServerManager\Http\Controllers\TerminalWebSocketController;

Route::middleware(config('server-manager.api.middleware', ['api', 'auth:sanctum']))
    ->prefix(config('server-manager.api.prefix', 'api/server-manager'))
    ->group(function () {
        // Server management
        Route::apiResource('servers', ServerController::class);
        Route::post('servers/{server}/test-connection', [ServerController::class, 'testConnection']);

        // Session management
        Route::apiResource('sessions', SessionController::class)->except(['update']);
        Route::post('sessions/{session}/share', [SessionController::class, 'share']);
        Route::delete('sessions/{session}/share/{user}', [SessionController::class, 'unshare']);

        // Command execution
        Route::post('sessions/{session}/execute', [CommandController::class, 'execute']);
        Route::post('sessions/{session}/execute-async', [CommandController::class, 'executeAsync']);
        Route::get('sessions/{session}/processes/{processId}/output', [CommandController::class, 'getOutput']);
        Route::get('sessions/{session}/processes/{processId}/status', [CommandController::class, 'getStatus']);
        Route::delete('sessions/{session}/processes/{processId}', [CommandController::class, 'killProcess']);

        // Command history
        Route::get('sessions/{session}/history', [CommandController::class, 'history']);

        // WebSocket endpoints
        Route::post('sessions/{session}/terminal/execute', [TerminalWebSocketController::class, 'execute']);
        Route::post('sessions/{session}/terminal/resize', [TerminalWebSocketController::class, 'resize']);
    });
