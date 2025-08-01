<?php

namespace Metacomet\ServerManager\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Metacomet\ServerManager\Models\Server;

class ServerPolicy
{
    use HandlesAuthorization;

    public function view(mixed $user, Server $server): bool
    {
        return (string) $server->user_id === (string) (is_object($user) && property_exists($user, 'id') ? $user->id : null);
    }

    public function update(mixed $user, Server $server): bool
    {
        return (string) $server->user_id === (string) (is_object($user) && property_exists($user, 'id') ? $user->id : null);
    }

    public function delete(mixed $user, Server $server): bool
    {
        return (string) $server->user_id === (string) (is_object($user) && property_exists($user, 'id') ? $user->id : null);
    }
}
