<?php

use Illuminate\Support\Facades\Broadcast;
use Metacomet\ServerManager\Models\Session;

$prefix = config('server-manager.broadcasting.channel_prefix', 'server-manager');

Broadcast::channel("{$prefix}.session.{session}", function ($user, Session $session) {
    return $session->canUserAccess($user->id);
});
