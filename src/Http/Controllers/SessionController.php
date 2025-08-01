<?php

namespace Metacomet\ServerManager\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Metacomet\ServerManager\Models\Server;
use Metacomet\ServerManager\Models\Session;
use Metacomet\ServerManager\Models\SessionShare;
use Metacomet\ServerManager\Services\SessionManager;

class SessionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private SessionManager $sessionManager) {}

    public function index(Request $request): JsonResponse
    {
        $sessions = Session::where('user_id', $request->user()->id)
            ->orWhereHas('shares', function ($query) use ($request) {
                $query->where('shared_with_user_id', $request->user()->id)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
            })
            ->with(['server', 'user'])
            ->paginate();

        return response()->json($sessions);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'server_id' => 'required|exists:'.config('server-manager.tables.servers').',id',
            'name' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        $server = Server::findOrFail($validated['server_id']);
        $this->authorize('view', $server);

        // Check session limit
        $activeSessionCount = Session::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->count();

        if ($activeSessionCount >= config('server-manager.sessions.max_per_user', 10)) {
            return response()->json([
                'message' => 'Maximum number of active sessions reached',
            ], 422);
        }

        $session = $this->sessionManager->createSession(
            (string) $request->user()->id,
            $server,
            $validated['name'] ?? null,
            $validated['metadata'] ?? []
        );

        return response()->json($session->load(['server', 'user']), 201);
    }

    public function show(Request $request, Session $session): JsonResponse
    {
        if (! $session->canUserAccess($request->user()->id)) {
            abort(403);
        }

        return response()->json($session->load(['server', 'user', 'shares.sharedWithUser']));
    }

    public function destroy(Request $request, Session $session): JsonResponse
    {
        $this->authorize('delete', $session);

        $this->sessionManager->destroySession($session);

        return response()->json(null, 204);
    }

    public function share(Request $request, Session $session): JsonResponse
    {
        $this->authorize('share', $session);

        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|in:view,execute',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($validated['user_id'] == $session->user_id) {
            return response()->json([
                'message' => 'Cannot share session with yourself',
            ], 422);
        }

        $share = SessionShare::updateOrCreate(
            [
                'session_id' => $session->id,
                'shared_with_user_id' => $validated['user_id'],
            ],
            [
                'shared_by_user_id' => $request->user()->id,
                'permission' => $validated['permission'],
                'expires_at' => $validated['expires_at'] ?? null,
            ]
        );

        $session->update(['is_shared' => true]);

        return response()->json($share->load('sharedWithUser'));
    }

    public function unshare(Request $request, Session $session, string|int $userId): JsonResponse
    {
        $this->authorize('share', $session);

        SessionShare::where('session_id', $session->id)
            ->where('shared_with_user_id', $userId)
            ->delete();

        // Check if session still has any shares
        if ($session->shares()->count() === 0) {
            $session->update(['is_shared' => false]);
        }

        return response()->json(null, 204);
    }
}
