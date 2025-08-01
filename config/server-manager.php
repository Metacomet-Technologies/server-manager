<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSH Connection Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the SSH connection driver used by the package.
    | 
    | Supported drivers:
    | - "phpseclib": Pure PHP implementation, works everywhere but slower
    | - "ssh2": PHP extension, faster but requires ext-ssh2 to be installed
    |
    | You can set this via the SERVER_MANAGER_SSH_DRIVER environment variable.
    |
    */
    'ssh_driver' => env('SERVER_MANAGER_SSH_DRIVER', 'phpseclib'),

    /*
    |--------------------------------------------------------------------------
    | WebSocket Support
    |--------------------------------------------------------------------------
    |
    | Enable WebSocket support for real-time terminal sessions.
    | When disabled, only REST API endpoints will be available.
    |
    | You can set this via the SERVER_MANAGER_WEBSOCKET_ENABLED environment variable.
    |
    */
    'websocket_enabled' => env('SERVER_MANAGER_WEBSOCKET_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | WebSocket Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WebSocket server when enabled.
    |
    */
    'websocket' => [
        /*
        | The host address to bind the WebSocket server to.
        | Default: '127.0.0.1' (localhost only)
        | Use '0.0.0.0' to listen on all interfaces
        */
        'host' => env('SERVER_MANAGER_WEBSOCKET_HOST', '127.0.0.1'),
        
        /*
        | The port number for the WebSocket server.
        | Default: 6001
        */
        'port' => env('SERVER_MANAGER_WEBSOCKET_PORT', 6001),
        
        'ssl' => [
            /*
            | Enable SSL/TLS for WebSocket connections.
            */
            'enabled' => env('SERVER_MANAGER_WEBSOCKET_SSL_ENABLED', false),
            
            /*
            | Path to SSL certificate file.
            */
            'cert' => env('SERVER_MANAGER_WEBSOCKET_SSL_CERT'),
            
            /*
            | Path to SSL private key file.
            */
            'key' => env('SERVER_MANAGER_WEBSOCKET_SSL_KEY'),
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
    | You can set this via the SERVER_MANAGER_LOCAL_GATE environment variable.
    |
    */
    'local_server_gate' => env('SERVER_MANAGER_LOCAL_GATE', 'server-manager:access-local'),

    /*
    |--------------------------------------------------------------------------
    | Session Sharing
    |--------------------------------------------------------------------------
    |
    | Default session sharing behavior. Users can override this per session.
    |
    */
    'session_sharing' => [
        /*
        | Whether sessions are private by default.
        | When true, sessions must be explicitly shared.
        */
        'default_private' => env('SERVER_MANAGER_DEFAULT_PRIVATE_SESSIONS', true),
        
        /*
        | Whether to allow session sharing at all.
        | When false, all sessions are forced to be private.
        */
        'allow_sharing' => env('SERVER_MANAGER_ALLOW_SESSION_SHARING', true),
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
        /*
        | Default command timeout in seconds.
        | Commands will be terminated if they exceed this limit.
        | Default: 300 (5 minutes)
        */
        'timeout' => env('SERVER_MANAGER_COMMAND_TIMEOUT', 300),
        
        /*
        | Maximum output size in bytes.
        | Commands producing more output will be truncated.
        | Default: 10485760 (10MB)
        */
        'max_output_size' => env('SERVER_MANAGER_MAX_OUTPUT_SIZE', 10 * 1024 * 1024),
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
        /*
        | Session Time-To-Live in seconds.
        | Inactive sessions will be terminated after this period.
        | Default: 3600 (1 hour)
        */
        'ttl' => env('SERVER_MANAGER_SESSION_TTL', 3600),
        
        /*
        | Maximum concurrent sessions per user.
        | Prevents resource exhaustion from too many open sessions.
        | Default: 10
        */
        'max_per_user' => env('SERVER_MANAGER_MAX_SESSIONS_PER_USER', 10),
        
        /*
        | Cleanup interval in seconds.
        | How often to check for and remove expired sessions.
        | Default: 300 (5 minutes)
        */
        'cleanup_interval' => env('SERVER_MANAGER_CLEANUP_INTERVAL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | The table names used by the package.
    | You can customize these if they conflict with existing tables.
    |
    */
    'tables' => [
        'servers' => env('SERVER_MANAGER_SERVERS_TABLE', 'sm_servers'),
        'sessions' => env('SERVER_MANAGER_SESSIONS_TABLE', 'sm_sessions'),
        'session_shares' => env('SERVER_MANAGER_SESSION_SHARES_TABLE', 'sm_session_shares'),
        'command_history' => env('SERVER_MANAGER_COMMAND_HISTORY_TABLE', 'sm_command_history'),
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
        /*
        | API route prefix.
        | All API routes will be prefixed with this value.
        | Default: 'api/server-manager'
        */
        'prefix' => env('SERVER_MANAGER_API_PREFIX', 'api/server-manager'),
        
        /*
        | Middleware stack for API routes.
        | Add your own middleware as needed for authentication, rate limiting, etc.
        */
        'middleware' => explode(',', (string) env('SERVER_MANAGER_API_MIDDLEWARE', 'api,auth:sanctum')),
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
        /*
        | Enable web interface routes.
        | Set to false if you only want to use the API.
        */
        'enabled' => env('SERVER_MANAGER_WEB_ENABLED', true),
        
        /*
        | Web route prefix.
        | All web routes will be prefixed with this value.
        | Default: 'server-manager'
        */
        'prefix' => env('SERVER_MANAGER_WEB_PREFIX', 'server-manager'),
        
        /*
        | Middleware stack for web routes.
        | Typically includes 'web' and authentication middleware.
        */
        'middleware' => explode(',', (string) env('SERVER_MANAGER_WEB_MIDDLEWARE', 'web,auth')),
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
        /*
        | Broadcasting driver to use.
        | 
        | Supported drivers:
        | - "reverb": Laravel Reverb (recommended)
        | - "pusher": Pusher service
        | - "ably": Ably service
        | - "redis": Redis pub/sub
        | - "log": Log driver (for development)
        | - "null": Disable broadcasting
        */
        'driver' => env('SERVER_MANAGER_BROADCAST_DRIVER', 'reverb'),
        
        /*
        | Channel name prefix for all broadcast channels.
        | Helps avoid conflicts with other packages.
        */
        'channel_prefix' => env('SERVER_MANAGER_CHANNEL_PREFIX', 'server-manager'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend Assets
    |--------------------------------------------------------------------------
    |
    | Whether to use the package's built-in frontend assets.
    | Set to false if you want to build your own frontend.
    |
    */
    'use_frontend' => env('SERVER_MANAGER_USE_FRONTEND', true),
];
