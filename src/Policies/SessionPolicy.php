<?php

namespace Metacomet\ServerManager\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Metacomet\ServerManager\Models\Session;

class SessionPolicy
{
    use HandlesAuthorization;

    public function view(mixed $user, Session $session): bool
    {
        return $session->canUserAccess(is_object($user) && property_exists($user, 'id') ? $user->id : null);
    }

    public function delete(mixed $user, Session $session): bool
    {
        return (string) $session->user_id === (string) (is_object($user) && property_exists($user, 'id') ? $user->id : null);
    }

    public function share(mixed $user, Session $session): bool
    {
        return (string) $session->user_id === (string) (is_object($user) && property_exists($user, 'id') ? $user->id : null);
    }

    public function execute(mixed $user, Session $session): bool
    {
        return $session->canUserExecute(is_object($user) && property_exists($user, 'id') ? $user->id : null);
    }
}
