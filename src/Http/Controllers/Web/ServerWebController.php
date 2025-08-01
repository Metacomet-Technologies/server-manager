<?php

namespace Metacomet\ServerManager\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Metacomet\ServerManager\Models\Server;
use Metacomet\ServerManager\Services\SessionManager;

class ServerWebController
{
    public function __construct(
        protected SessionManager $sessionManager
    ) {}

    public function index(): \Inertia\Response
    {
        $servers = Server::where('user_id', auth()->id())
            ->latest()
            ->get();

        $localServerEnabled = Gate::allows(config('server-manager.local_server_gate', 'server-manager:access-local'));

        return Inertia::render('ServerManager/Servers/Index', [
            'servers' => $servers,
            'localServerEnabled' => $localServerEnabled,
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('ServerManager/Servers/Create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'auth_type' => 'required|in:password,key',
            'password' => 'required_if:auth_type,password|nullable|string',
            'private_key' => 'required_if:auth_type,key|nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        Server::create($validated);

        return redirect()->route('server-manager.servers.index')
            ->with('success', 'Server added successfully');
    }

    public function show(Server $server): \Inertia\Response
    {
        $this->authorize('view', $server);

        return Inertia::render('ServerManager/Servers/Show', [
            'server' => $server,
        ]);
    }

    public function edit(Server $server): \Inertia\Response
    {
        $this->authorize('update', $server);

        return Inertia::render('ServerManager/Servers/Edit', [
            'server' => $server,
        ]);
    }

    public function update(Request $request, Server $server): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $server);

        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'auth_type' => 'required|in:password,key',
            'password' => 'nullable|string',
            'private_key' => 'nullable|string',
        ]);

        // Only update password/key if provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        if (empty($validated['private_key'])) {
            unset($validated['private_key']);
        }

        $server->update($validated);

        return redirect()->route('server-manager.servers.index')
            ->with('success', 'Server updated successfully');
    }

    public function destroy(Server $server): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $server);

        // Delete related sessions
        $server->sessions()->delete();
        $server->delete();

        return redirect()->route('server-manager.servers.index')
            ->with('success', 'Server deleted successfully');
    }

    public function testConnection(Server $server): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $server);

        try {
            $connection = $this->sessionManager->createConnection($server);
            $connection->connect();
            $connection->disconnect();

            return response()->json([
                'success' => true,
                'message' => 'Connection successful',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    protected function authorize(string $ability, mixed $resource): void
    {
        if (! Gate::allows($ability, $resource)) {
            abort(403);
        }
    }
}
