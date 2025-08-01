<?php

namespace Metacomet\ServerManager\Http\Controllers\Web;

use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Metacomet\ServerManager\Models\Session;

class TerminalController
{
    public function show(Session $session): \Inertia\Response
    {
        $this->authorize('view', $session);

        // Check if user can execute commands
        $canExecute = false;
        $userId = auth()->id();

        if ($session->user_id == $userId) {
            $canExecute = true;
        } else {
            $share = $session->sharedUsers()
                ->where('user_id', $userId)
                ->first();
            if ($share && $share->pivot->can_execute) {
                $canExecute = true;
            }
        }

        return Inertia::render('ServerManager/Terminal', [
            'session' => $session->load('server'),
            'canExecute' => $canExecute,
        ]);
    }

    protected function authorize(string $ability, mixed $resource): void
    {
        if (! Gate::allows($ability, $resource)) {
            abort(403);
        }
    }
}
