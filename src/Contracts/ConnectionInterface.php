<?php

namespace Metacomet\ServerManager\Contracts;

interface ConnectionInterface
{
    public function connect(): void;

    public function disconnect(): void;

    public function isConnected(): bool;

    /** @return array<string, mixed> */
    public function execute(string $command): array;

    public function executeAsync(string $command): string;

    public function getOutput(string $processId): ?string;

    public function isProcessRunning(string $processId): bool;

    public function killProcess(string $processId): bool;
}
