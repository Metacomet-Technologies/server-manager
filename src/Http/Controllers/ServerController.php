<?php

namespace Metacomet\ServerManager\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Metacomet\ServerManager\Models\Server;
use Metacomet\ServerManager\Services\ConnectionFactory;

class ServerController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $servers = Server::where('user_id', $request->user()->id)
            ->paginate();

        return response()->json($servers);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required_unless:is_local,true|string|max:255',
            'port' => 'integer|min:1|max:65535',
            'username' => 'required_unless:is_local,true|string|max:255',
            'auth_type' => 'required_unless:is_local,true|in:password,key,both',
            'password' => 'required_if:auth_type,password|nullable|string',
            'private_key' => 'required_if:auth_type,key|nullable|string',
            'key_passphrase' => 'nullable|string',
            'is_local' => 'boolean',
            'metadata' => 'nullable|array',
        ]);

        $server = Server::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'port' => $validated['port'] ?? 22,
        ]);

        return response()->json($server, 201);
    }

    public function show(Request $request, Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        return response()->json($server);
    }

    public function update(Request $request, Server $server): JsonResponse
    {
        $this->authorize('update', $server);

        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'name' => 'string|max:255',
            'host' => 'string|max:255',
            'port' => 'integer|min:1|max:65535',
            'username' => 'string|max:255',
            'auth_type' => 'in:password,key,both',
            'password' => 'nullable|string',
            'private_key' => 'nullable|string',
            'key_passphrase' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        $server->update($validated);

        return response()->json($server);
    }

    public function destroy(Server $server): JsonResponse
    {
        $this->authorize('delete', $server);

        $server->delete();

        return response()->json(null, 204);
    }

    public function testConnection(Request $request, Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        try {
            $connection = ConnectionFactory::create($server->connectionConfig);
            $connection->connect();
            $result = $connection->execute('echo "Connection successful"');
            $connection->disconnect();

            return response()->json([
                'success' => true,
                'message' => $result['output'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
