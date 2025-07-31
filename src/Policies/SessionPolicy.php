<?php

namespace Metacomet\ServerManager\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Metacomet\ServerManager\Models\Session;

class SessionPolicy
{
    use HandlesAuthorization;

    public function view($user, Session $session): bool
    {
        return $session->canUserAccess($user->id);
    }

    public function delete($user, Session $session): bool
    {
        return (string) $session->user_id === (string) $user->id;
    }

    public function share($user, Session $session): bool
    {
        return (string) $session->user_id === (string) $user->id;
    }

    public function execute($user, Session $session): bool
    {
        return $session->canUserExecute($user->id);
    }
}
