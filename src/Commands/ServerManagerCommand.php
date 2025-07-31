<?php

namespace Metacomet\ServerManager\Commands;

use Illuminate\Console\Command;

class ServerManagerCommand extends Command
{
    public $signature = 'server-manager';

    public $description = 'Manage local and remote servers';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
