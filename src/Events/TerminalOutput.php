<?php

namespace Metacomet\ServerManager\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Metacomet\ServerManager\Models\Session;

class TerminalOutput implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Session $session,
        public string $output,
        public string $type = 'output' // output, error, or status
    ) {}

    public function broadcastOn(): Channel
    {
        $prefix = config('server-manager.broadcasting.channel_prefix', 'server-manager');

        return new PrivateChannel("{$prefix}.session.{$this->session->id}");
    }

    public function broadcastAs(): string
    {
        return 'terminal.output';
    }

    public function broadcastWith(): array
    {
        return [
            'output' => $this->output,
            'type' => $this->type,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
