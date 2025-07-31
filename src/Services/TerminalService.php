<?php

namespace Metacomet\ServerManager\Services;

use Metacomet\ServerManager\Events\TerminalOutput;
use Metacomet\ServerManager\Models\CommandHistory;
use Metacomet\ServerManager\Models\Session;

class TerminalService
{
    public function __construct(private SessionManager $sessionManager) {}

    public function executeCommand(Session $session, string $command, $userId): void
    {
        $connection = $this->sessionManager->getConnection($session);

        // Broadcast the command being executed
        broadcast(new TerminalOutput($session, "$ {$command}\r\n", 'input'));

        // Execute in a separate process to allow streaming
        $processId = $connection->executeAsync($command);

        // Store command history
        $history = CommandHistory::create([
            'session_id' => $session->id,
            'user_id' => $userId,
            'command' => $command,
            'output' => null,
            'exit_code' => null,
            'duration_ms' => null,
        ]);

        // Start output streaming
        $this->streamOutput($session, $processId, $history);
    }

    private function streamOutput(Session $session, string $processId, CommandHistory $history): void
    {
        $connection = $this->sessionManager->getConnection($session);
        $buffer = '';
        $startTime = microtime(true);

        while ($connection->isProcessRunning($processId)) {
            $output = $connection->getOutput($processId);

            if ($output && $output !== $buffer) {
                $newOutput = substr($output, strlen($buffer));
                $buffer = $output;

                // Broadcast new output
                if ($newOutput) {
                    broadcast(new TerminalOutput($session, $newOutput, 'output'));
                }
            }

            usleep(100000); // 100ms delay
        }

        // Get final output
        $finalOutput = $connection->getOutput($processId);
        if ($finalOutput && $finalOutput !== $buffer) {
            $newOutput = substr($finalOutput, strlen($buffer));
            broadcast(new TerminalOutput($session, $newOutput, 'output'));
        }

        // Update command history
        $duration = (int) ((microtime(true) - $startTime) * 1000);
        $history->update([
            'output' => $finalOutput,
            'duration_ms' => $duration,
        ]);

        // Touch session activity
        $this->sessionManager->touchSession($session);
    }

    public function resize(Session $session, int $cols, int $rows): void
    {
        // Store terminal size in session metadata
        $metadata = $session->metadata ?? [];
        $metadata['terminal'] = [
            'cols' => $cols,
            'rows' => $rows,
        ];
        $session->update(['metadata' => $metadata]);
    }
}
