<?php

namespace Metacomet\TmuxManager\Commands;

use Illuminate\Console\Command;

class TmuxManagerCommand extends Command
{
    public $signature = 'tmux-manager';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
