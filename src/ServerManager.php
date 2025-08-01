<?php

namespace Metacomet\ServerManager;

use Metacomet\ServerManager\Models\Server;
use Metacomet\ServerManager\Models\Session;
use Metacomet\ServerManager\Services\ConnectionFactory;
use Metacomet\ServerManager\Services\SessionManager;

class ServerManager
{
    public function __construct(private SessionManager $sessionManager) {}

    /** @return \Illuminate\Database\Eloquent\Builder<Server> */
    public function servers(): \Illuminate\Database\Eloquent\Builder
    {
        return Server::query();
    }

    /** @return \Illuminate\Database\Eloquent\Builder<Session> */
    public function sessions(): \Illuminate\Database\Eloquent\Builder
    {
        return Session::query();
    }

    /** @param array<string, mixed> $data */
    public function createServer(array $data): Server
    {
        return Server::create($data);
    }

    /** @param array<string, mixed> $metadata */
    public function createSession(string $userId, Server $server, ?string $name = null, array $metadata = []): Session
    {
        return $this->sessionManager->createSession($userId, $server, $name, $metadata);
    }

    /** @return array<string, mixed> */
    public function testConnection(Server $server): array
    {
        try {
            $connection = ConnectionFactory::create($server->connectionConfig);
            $connection->connect();
            $result = $connection->execute('echo "Connection successful"');
            $connection->disconnect();

            return [
                'success' => true,
                'message' => trim(is_string($result['output']) ? $result['output'] : ''),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /** @return array<string, mixed> */
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
