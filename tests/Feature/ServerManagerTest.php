<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Metacomet\ServerManager\Models\Server;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Run package migrations
    $this->artisan('migrate', ['--database' => 'testing'])->run();
});

it('can create a server', function () {
    $userId = 1;

    $server = Server::create([
        'user_id' => $userId,
        'name' => 'Test Server',
        'host' => 'localhost',
        'port' => 22,
        'username' => 'test',
        'auth_type' => 'password',
        'password' => 'test123',
        'is_local' => true,
    ]);

    expect($server)->toBeInstanceOf(Server::class);
    expect($server->name)->toBe('Test Server');
    expect($server->user_id)->toBe($userId);
});

it('encrypts sensitive data', function () {
    $userId = 1;

    $server = Server::create([
        'user_id' => $userId,
        'name' => 'Test Server',
        'host' => 'localhost',
        'port' => 22,
        'username' => 'test',
        'auth_type' => 'password',
        'password' => 'test123',
        'is_local' => true,
    ]);

    // Refresh to get encrypted values from database
    $server->refresh();

    // Password should be encrypted in database
    $rawPassword = $server->getAttributes()['password'];
    expect($rawPassword)->not->toBe('test123');

    // But decrypted when accessed
    expect($server->password)->toBe('test123');
});

it('uses uuid as primary key', function () {
    $userId = 1;

    $server = Server::create([
        'user_id' => $userId,
        'name' => 'Test Server',
        'host' => 'localhost',
        'port' => 22,
        'username' => 'test',
        'auth_type' => 'key',
        'private_key' => 'test-key',
        'is_local' => true,
    ]);

    expect($server->id)->toBeString();
    expect(strlen($server->id))->toBe(36); // UUID length
});
