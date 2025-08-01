<?php

namespace Metacomet\ServerManager\Connections;

use Metacomet\ServerManager\Contracts\ConnectionInterface;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;

class PhpSeclibConnection implements ConnectionInterface
{
    private SSH2 $ssh;

    /** @var array<string, mixed> */
    private array $connectionConfig;

    /** @var array<string, string> */
    private array $runningProcesses = [];

    /** @param array<string, mixed> $connectionConfig */
    public function __construct(array $connectionConfig)
    {
        $this->connectionConfig = $connectionConfig;
        $this->ssh = new SSH2($connectionConfig['host'], $connectionConfig['port'] ?? 22);
    }

    public function connect(): void
    {
        $authenticated = false;

        if ($this->connectionConfig['auth_type'] === 'key' || $this->connectionConfig['auth_type'] === 'both') {
            if (! empty($this->connectionConfig['private_key'])) {
                $key = PublicKeyLoader::load(
                    $this->connectionConfig['private_key'],
                    $this->connectionConfig['key_passphrase'] ?? ''
                );

                if ($this->ssh->login($this->connectionConfig['username'], $key)) {
                    $authenticated = true;
                }
            }
        }

        if (! $authenticated && ($this->connectionConfig['auth_type'] === 'password' || $this->connectionConfig['auth_type'] === 'both')) {
            if (! empty($this->connectionConfig['password'])) {
                if ($this->ssh->login($this->connectionConfig['username'], $this->connectionConfig['password'])) {
                    $authenticated = true;
                }
            }
        }

        if (! $authenticated) {
            throw new \RuntimeException('Failed to authenticate with server');
        }
    }

    public function disconnect(): void
    {
        foreach ($this->runningProcesses as $processId => $channelId) {
            $this->killProcess($processId);
        }

        $this->ssh->disconnect();
        $this->runningProcesses = [];
    }

    public function isConnected(): bool
    {
        return $this->ssh->isConnected();
    }

    /** @return array<string, mixed> */
    public function execute(string $command): array
    {
        if (! $this->isConnected()) {
            throw new \RuntimeException('Not connected to server');
        }

        $output = $this->ssh->exec($command);
        $exitCode = $this->ssh->getExitStatus();

        return [
            'output' => $output,
            'error' => $this->ssh->getStdError() ?: '',
            'exit_code' => $exitCode !== false ? $exitCode : -1,
        ];
    }

    public function executeAsync(string $command): string
    {
        if (! $this->isConnected()) {
            throw new \RuntimeException('Not connected to server');
        }

        $processId = uniqid('proc_');

        // For phpseclib, we'll use a background process with nohup
        $bgCommand = sprintf('nohup %s > /tmp/%s.out 2>&1 & echo $!', $command, $processId);
        $result = $this->ssh->exec($bgCommand);

        if ($result !== false) {
            $pid = trim($result);
            $this->runningProcesses[$processId] = $pid;
        }

        return $processId;
    }

    public function getOutput(string $processId): ?string
    {
        if (! isset($this->runningProcesses[$processId])) {
            return null;
        }

        $outputFile = "/tmp/{$processId}.out";
        $result = $this->execute("cat {$outputFile} 2>/dev/null || echo ''");

        return $result['output'];
    }

    public function isProcessRunning(string $processId): bool
    {
        if (! isset($this->runningProcesses[$processId])) {
            return false;
        }

        $pid = $this->runningProcesses[$processId];
        $result = $this->execute("ps -p {$pid} > /dev/null 2>&1 && echo 'running' || echo 'stopped'");

        return trim($result['output']) === 'running';
    }

    public function killProcess(string $processId): bool
    {
        if (! isset($this->runningProcesses[$processId])) {
            return false;
        }

        $pid = $this->runningProcesses[$processId];
        $this->execute("kill -9 {$pid} 2>/dev/null");

        // Clean up output file
        $outputFile = "/tmp/{$processId}.out";
        $this->execute("rm -f {$outputFile} 2>/dev/null");

        unset($this->runningProcesses[$processId]);

        return true;
    }
}
