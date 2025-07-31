<?php

namespace Metacomet\ServerManager;

use Metacomet\ServerManager\Models\Server;
use Metacomet\ServerManager\Models\Session;
use Metacomet\ServerManager\Services\ConnectionFactory;
use Metacomet\ServerManager\Services\SessionManager;

class ServerManager
{
    public function __construct(private SessionManager $sessionManager) {}

    public function servers(): \Illuminate\Database\Eloquent\Builder
    {
        return Server::query();
    }

    public function sessions(): \Illuminate\Database\Eloquent\Builder
    {
        return Session::query();
    }

    public function createServer(array $data): Server
    {
        return Server::create($data);
    }

    public function createSession(string $userId, Server $server, ?string $name = null, array $metadata = []): Session
    {
        return $this->sessionManager->createSession($userId, $server, $name, $metadata);
    }

    public function testConnection(Server $server): array
    {
        try {
            $connection = ConnectionFactory::create($server->connectionConfig);
            $connection->connect();
            $result = $connection->execute('echo "Connection successful"');
            $connection->disconnect();

            return [
                'success' => true,
                'message' => trim($result['output']),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function execute(Session $session, string $command): array
    {
        $connection = $this->sessionManager->getConnection($session);

        return $connection->execute($command);
    }

    public function executeAsync(Session $session, string $command): string
    {
        $connection = $this->sessionManager->getConnection($session);

        return $connection->executeAsync($command);
    }

    public function cleanupInactiveSessions(): int
    {
        return $this->sessionManager->cleanupInactiveSessions();
    }
}
