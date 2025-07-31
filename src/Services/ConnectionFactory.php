<?php

namespace Metacomet\ServerManager\Services;

use Illuminate\Support\Facades\Gate;
use Metacomet\ServerManager\Connections\LocalConnection;
use Metacomet\ServerManager\Connections\PhpSeclibConnection;
use Metacomet\ServerManager\Connections\Ssh2Connection;
use Metacomet\ServerManager\Contracts\ConnectionInterface;

class ConnectionFactory
{
    public static function create(array $serverConfig): ConnectionInterface
    {
        if ($serverConfig['is_local'] ?? false) {
            // Check gate for local server access
            if (! Gate::allows(config('server-manager.local_server_gate'))) {
                throw new \RuntimeException('Access denied to local server');
            }

            return new LocalConnection;
        }

        $driver = config('server-manager.ssh_driver', 'phpseclib');

        return match ($driver) {
            'ssh2' => new Ssh2Connection($serverConfig),
            'phpseclib' => new PhpSeclibConnection($serverConfig),
            default => throw new \InvalidArgumentException("Unknown SSH driver: {$driver}"),
        };
    }
}
