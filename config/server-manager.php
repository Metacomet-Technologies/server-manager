<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSH Connection Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the SSH connection driver used by the package.
    | Supported: "phpseclib", "ssh2"
    |
    | phpseclib: Pure PHP implementation, works everywhere but slower
    | ssh2: PHP extension, faster but requires ext-ssh2 to be installed
    |
    */
    'ssh_driver' => 'phpseclib',

    /*
    |--------------------------------------------------------------------------
    | WebSocket Support
    |--------------------------------------------------------------------------
    |
    | Enable WebSocket support for real-time terminal sessions.
    | When disabled, only REST API endpoints will be available.
    |
    */
    'websocket_enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | WebSocket Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WebSocket server when enabled.
    |
    */
    'websocket' => [
        'host' => '127.0.0.1',
        'port' => 6001,
        'ssl' => [
            'enabled' => false,
            'cert' => null,
            'key' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Local Server Access Gate
    |--------------------------------------------------------------------------
    |
    | The gate that must be passed to allow access to the local server.
    | This is a security measure to prevent unauthorized local server access.
    |
    */
    'local_server_gate' => 'server-manager:access-local',

    /*
    |--------------------------------------------------------------------------
    | Session Sharing
    |--------------------------------------------------------------------------
    |
    | Default session sharing behavior. Users can override this per session.
    |
    */
    'session_sharing' => [
        'default_private' => true,
        'allow_sharing' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Command Execution
    |--------------------------------------------------------------------------
    |
    | Configuration for command execution behavior.
    |
    */
    'commands' => [
        'timeout' => 300, // Default command timeout in seconds
        'max_output_size' => 10 * 1024 * 1024, // 10MB max output
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Management
    |--------------------------------------------------------------------------
    |
    | Configuration for session management.
    |
    */
    'sessions' => [
        'ttl' => 3600, // Session TTL in seconds (1 hour)
        'max_per_user' => 10, // Maximum concurrent sessions per user
        'cleanup_interval' => 300, // Cleanup interval in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | The table names used by the package.
    |
    */
    'tables' => [
        'servers' => 'sm_servers',
        'sessions' => 'sm_sessions',
        'session_shares' => 'sm_session_shares',
        'command_history' => 'sm_command_history',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the REST API endpoints.
    |
    */
    'api' => [
        'prefix' => 'api/server-manager',
        'middleware' => ['api', 'auth:sanctum'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Web Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the web interface routes.
    |
    */
    'web' => [
        'enabled' => true,
        'prefix' => 'server-manager',
        'middleware' => ['web', 'auth'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Broadcasting Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WebSocket broadcasting.
    |
    */
    'broadcasting' => [
        'driver' => 'reverb',
        'channel_prefix' => 'server-manager',
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend Assets
    |--------------------------------------------------------------------------
    |
    | Whether to use the package's built-in frontend assets.
    |
    */
    'use_frontend' => true,
];
