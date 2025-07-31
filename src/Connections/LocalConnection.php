<?php

namespace Metacomet\ServerManager\Connections;

use Metacomet\ServerManager\Contracts\ConnectionInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class LocalConnection implements ConnectionInterface
{
    private array $runningProcesses = [];

    private bool $connected = false;

    public function connect(): void
    {
        $this->connected = true;
    }

    public function disconnect(): void
    {
        foreach ($this->runningProcesses as $processId => $process) {
            if ($process->isRunning()) {
                $process->stop();
            }
        }

        $this->runningProcesses = [];
        $this->connected = false;
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function execute(string $command): array
    {
        if (! $this->connected) {
            throw new \RuntimeException('Not connected to local server');
        }

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(config('server-manager.commands.timeout', 300));

        try {
            $process->run();

            return [
                'output' => $process->getOutput(),
                'error' => $process->getErrorOutput(),
                'exit_code' => $process->getExitCode(),
            ];
        } catch (ProcessFailedException $e) {
            return [
                'output' => $e->getProcess()->getOutput(),
                'error' => $e->getProcess()->getErrorOutput(),
                'exit_code' => $e->getProcess()->getExitCode(),
            ];
        }
    }

    public function executeAsync(string $command): string
    {
        if (! $this->connected) {
            throw new \RuntimeException('Not connected to local server');
        }

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null);
        $process->start();

        $processId = uniqid('proc_');
        $this->runningProcesses[$processId] = $process;

        return $processId;
    }

    public function getOutput(string $processId): ?string
    {
        if (! isset($this->runningProcesses[$processId])) {
            return null;
        }

        $process = $this->runningProcesses[$processId];

        return $process->getIncrementalOutput().$process->getIncrementalErrorOutput();
    }

    public function isProcessRunning(string $processId): bool
    {
        if (! isset($this->runningProcesses[$processId])) {
            return false;
        }

        return $this->runningProcesses[$processId]->isRunning();
    }

    public function killProcess(string $processId): bool
    {
        if (! isset($this->runningProcesses[$processId])) {
            return false;
        }

        $process = $this->runningProcesses[$processId];

        if ($process->isRunning()) {
            $process->stop();
        }

        unset($this->runningProcesses[$processId]);

        return true;
    }
}
