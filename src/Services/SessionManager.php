<?php

namespace Metacomet\ServerManager\Services;

use Metacomet\ServerManager\Contracts\ConnectionInterface;
use Metacomet\ServerManager\Models\Server;
use Metacomet\ServerManager\Models\Session;

class SessionManager
{
    /** @var array<string, ConnectionInterface> */
    private array $connections = [];

    /** @param array<string, mixed> $metadata */
    public function createSession(string $userId, Server $server, ?string $name = null, array $metadata = []): Session
    {
        $session = Session::create([
            'user_id' => $userId,
            'server_id' => $server->id,
            'name' => $name,
            'is_active' => true,
            'is_shared' => false,
            'metadata' => $metadata,
            'last_activity_at' => now(),
        ]);

        // Initialize connection
        $this->getConnection($session);

        return $session;
    }

    public function destroySession(Session $session): void
    {
        // Disconnect if connected
        if (isset($this->connections[$session->id])) {
            $this->connections[$session->id]->disconnect();
            unset($this->connections[$session->id]);
        }

        $session->update(['is_active' => false]);
        $session->delete();
    }

    public function getConnection(Session $session): ConnectionInterface
    {
        if (! isset($this->connections[$session->id])) {
            $connection = ConnectionFactory::create($session->server->connectionConfig);
            $connection->connect();
            $this->connections[$session->id] = $connection;
        }

        return $this->connections[$session->id];
    }

    public function touchSession(Session $session): void
    {
        $session->update(['last_activity_at' => now()]);
    }

    public function cleanupInactiveSessions(): int
    {
        $ttl = config('server-manager.sessions.ttl', 3600);
        $cutoff = now()->subSeconds(is_int($ttl) ? $ttl : (is_numeric($ttl) ? (int) $ttl : 3600));

        $inactiveSessions = Session::where('is_active', true)
            ->where('last_activity_at', '<', $cutoff)
            ->get();

        foreach ($inactiveSessions as $session) {
            $this->destroySession($session);
        }

        return $inactiveSessions->count();
    }
}
