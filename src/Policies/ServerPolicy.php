<?php

namespace Metacomet\ServerManager\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Metacomet\ServerManager\Models\Server;

class ServerPolicy
{
    use HandlesAuthorization;

    public function view($user, Server $server): bool
    {
        return (string) $server->user_id === (string) $user->id;
    }

    public function update($user, Server $server): bool
    {
        return (string) $server->user_id === (string) $user->id;
    }

    public function delete($user, Server $server): bool
    {
        return (string) $server->user_id === (string) $user->id;
    }
}
