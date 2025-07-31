<?php

namespace Metacomet\ServerManager\Commands;

use Illuminate\Console\Command;
use Metacomet\ServerManager\Services\SessionManager;

class CleanupSessionsCommand extends Command
{
    public $signature = 'server-manager:cleanup-sessions';

    public $description = 'Clean up inactive sessions';

    public function handle(SessionManager $sessionManager): int
    {
        $this->info('Cleaning up inactive sessions...');

        $count = $sessionManager->cleanupInactiveSessions();

        $this->info("Cleaned up {$count} inactive sessions.");

        return self::SUCCESS;
    }
}
