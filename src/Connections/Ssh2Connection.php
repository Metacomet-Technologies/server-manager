<?php

namespace Metacomet\ServerManager\Connections;

use const SSH2_STREAM_STDERR;

use Metacomet\ServerManager\Contracts\ConnectionInterface;

use function ssh2_auth_password;
use function ssh2_auth_pubkey_file;
use function ssh2_connect;
use function ssh2_disconnect;
use function ssh2_exec;
use function ssh2_fetch_stream;

class Ssh2Connection implements ConnectionInterface
{
    /** @var resource|null */
    private $connection;

    /** @var array<string, mixed> */
    private array $connectionConfig;

    /** @var array<string, string> */
    private array $runningProcesses = [];

    /** @param array<string, mixed> $connectionConfig */
    public function __construct(array $connectionConfig)
    {
        if (! extension_loaded('ssh2')) {
            throw new \RuntimeException('SSH2 extension is not installed');
        }

        $this->connectionConfig = $connectionConfig;
    }

    public function connect(): void
    {
        $this->connection = ssh2_connect(
            $this->connectionConfig['host'],
            $this->connectionConfig['port'] ?? 22
        );

        if (! $this->connection) {
            throw new \RuntimeException('Failed to connect to server');
        }

        $authenticated = false;

        if ($this->connectionConfig['auth_type'] === 'key' || $this->connectionConfig['auth_type'] === 'both') {
            if (! empty($this->connectionConfig['private_key'])) {
                // For SSH2 extension, we need the key as a file
                $keyFile = tempnam(sys_get_temp_dir(), 'ssh_key_');
                file_put_contents($keyFile, $this->connectionConfig['private_key']);
                chmod($keyFile, 0600);

                $pubKeyFile = $keyFile.'.pub';

                if (ssh2_auth_pubkey_file(
                    $this->connection,
                    $this->connectionConfig['username'],
                    $pubKeyFile,
                    $keyFile,
                    $this->connectionConfig['key_passphrase'] ?? ''
                )) {
                    $authenticated = true;
                }

                unlink($keyFile);
                if (file_exists($pubKeyFile)) {
                    unlink($pubKeyFile);
                }
            }
        }

        if (! $authenticated && ($this->connectionConfig['auth_type'] === 'password' || $this->connectionConfig['auth_type'] === 'both')) {
            if (! empty($this->connectionConfig['password'])) {
                if (ssh2_auth_password(
                    $this->connection,
                    $this->connectionConfig['username'],
                    $this->connectionConfig['password']
                )) {
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
        foreach ($this->runningProcesses as $processId => $stream) {
            $this->killProcess($processId);
        }

        if ($this->connection) {
            ssh2_disconnect($this->connection);
            $this->connection = null;
        }

        $this->runningProcesses = [];
    }

    public function isConnected(): bool
    {
        return $this->connection !== null;
    }

    /** @return array<string, mixed> */
    public function execute(string $command): array
    {
        if (! $this->isConnected()) {
            throw new \RuntimeException('Not connected to server');
        }

        $stream = ssh2_exec($this->connection, $command);

        if (! $stream) {
            throw new \RuntimeException('Failed to execute command');
        }

        stream_set_blocking($stream, true);

        $output = stream_get_contents($stream);
        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        $error = stream_get_contents($errorStream);

        fclose($stream);
        fclose($errorStream);

        // SSH2 doesn't provide exit codes directly, we'll use a workaround
        $exitCodeStream = ssh2_exec($this->connection, 'echo $?');
        stream_set_blocking($exitCodeStream, true);
        $exitCode = (int) trim(stream_get_contents($exitCodeStream));
        fclose($exitCodeStream);

        return [
            'output' => $output,
            'error' => $error,
            'exit_code' => $exitCode,
        ];
    }

    public function executeAsync(string $command): string
    {
        if (! $this->isConnected()) {
            throw new \RuntimeException('Not connected to server');
        }

        $processId = uniqid('proc_');

        // Execute command in background
        $bgCommand = sprintf('nohup %s > /tmp/%s.out 2>&1 & echo $!', $command, $processId);
        $stream = ssh2_exec($this->connection, $bgCommand);

        if (! $stream) {
            throw new \RuntimeException('Failed to execute async command');
        }

        stream_set_blocking($stream, true);
        $pid = trim(stream_get_contents($stream));
        fclose($stream);

        $this->runningProcesses[$processId] = $pid;

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
