<?php

namespace Metacomet\ServerManager\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Metacomet\ServerManager\Models\Server;
use Metacomet\ServerManager\Models\Session;
use Metacomet\ServerManager\Models\SessionShare;
use Metacomet\ServerManager\Services\SessionManager;

class SessionWebController
{
    public function __construct(
        protected SessionManager $sessionManager
    ) {}

    public function index()
    {
        $sessions = Session::with('server', 'sharedUsers')
            ->where(function ($query) {
                $query->where('user_id', auth()->id())
                    ->orWhereHas('sharedUsers', function ($q) {
                        $q->where('user_id', auth()->id());
                    });
            })
            ->latest()
            ->get();

        return Inertia::render('ServerManager/Sessions/Index', [
            'sessions' => $sessions,
        ]);
    }

    public function create(Request $request)
    {
        $serverId = $request->query('server_id');
        $server = null;

        if ($serverId && $serverId !== 'local') {
            $server = Server::findOrFail($serverId);
            $this->authorize('view', $server);
        }

        return Inertia::render('ServerManager/Sessions/Create', [
            'server' => $server,
            'server_id' => $serverId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|string',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validated['server_id'] === 'local') {
            // Check if user can access local server
            if (! Gate::allows(config('server-manager.local_server_gate', 'server-manager:access-local'))) {
                abort(403, 'You do not have permission to access the local server');
            }

            $session = $this->sessionManager->createLocalSession(
                auth()->id(),
                $validated['name'] ?? null
            );
        } else {
            $server = Server::findOrFail($validated['server_id']);
            $this->authorize('view', $server);

            $session = $this->sessionManager->createSession(
                auth()->id(),
                $server,
                $validated['name'] ?? null
            );
        }

        return redirect()->route('server-manager.terminal', $session->id);
    }

    public function destroy(Session $session)
    {
        $this->authorize('delete', $session);

        $this->sessionManager->endSession($session);

        return redirect()->route('server-manager.sessions.index')
            ->with('success', 'Session ended successfully');
    }

    public function share(Session $session)
    {
        $this->authorize('share', $session);

        // Get all users except the owner
        $users = \App\Models\User::where('id', '!=', $session->user_id)->get();

        // Get currently shared users
        $sharedUsers = SessionShare::with('user')
            ->where('session_id', $session->id)
            ->get();

        return Inertia::render('ServerManager/Sessions/Share', [
            'session' => $session->load('server'),
            'users' => $users,
            'sharedUsers' => $sharedUsers,
        ]);
    }

    public function storeShare(Request $request, Session $session)
    {
        $this->authorize('share', $session);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'can_execute' => 'boolean',
        ]);

        // Check if already shared
        $existing = SessionShare::where('session_id', $session->id)
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($existing) {
            $existing->update(['can_execute' => $validated['can_execute'] ?? false]);
        } else {
            SessionShare::create([
                'session_id' => $session->id,
                'user_id' => $validated['user_id'],
                'can_execute' => $validated['can_execute'] ?? false,
            ]);
        }

        return redirect()->route('server-manager.sessions.share', $session->id)
            ->with('success', 'Session shared successfully');
    }

    public function destroyShare(Session $session, $userId)
    {
        $this->authorize('share', $session);

        SessionShare::where('session_id', $session->id)
            ->where('user_id', $userId)
            ->delete();

        return redirect()->route('server-manager.sessions.share', $session->id)
            ->with('success', 'Access revoked successfully');
    }

    protected function authorize($ability, $resource)
    {
        if (! Gate::allows($ability, $resource)) {
            abort(403);
        }
    }
}
