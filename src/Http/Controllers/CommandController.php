<?php

namespace Metacomet\ServerManager\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Metacomet\ServerManager\Models\CommandHistory;
use Metacomet\ServerManager\Models\Session;
use Metacomet\ServerManager\Services\SessionManager;

class CommandController extends Controller
{
    public function __construct(private SessionManager $sessionManager) {}

    public function execute(Request $request, Session $session): JsonResponse
    {
        if (! $session->canUserExecute($request->user()->id)) {
            abort(403, 'You do not have permission to execute commands in this session');
        }

        $validated = $request->validate([
            'command' => 'required|string',
        ]);

        $startTime = microtime(true);

        try {
            $connection = $this->sessionManager->getConnection($session);
            $result = $connection->execute($validated['command']);

            $duration = (int) ((microtime(true) - $startTime) * 1000);

            // Store in history
            $history = CommandHistory::create([
                'session_id' => $session->id,
                'user_id' => $request->user()->id,
                'command' => $validated['command'],
                'output' => substr($result['output'].$result['error'], 0, config('server-manager.commands.max_output_size')),
                'exit_code' => $result['exit_code'],
                'duration_ms' => $duration,
            ]);

            $this->sessionManager->touchSession($session);

            return response()->json([
                'id' => $history->id,
                'output' => $result['output'],
                'error' => $result['error'],
                'exit_code' => $result['exit_code'],
                'duration_ms' => $duration,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function executeAsync(Request $request, Session $session): JsonResponse
    {
        if (! $session->canUserExecute($request->user()->id)) {
            abort(403, 'You do not have permission to execute commands in this session');
        }

        $validated = $request->validate([
            'command' => 'required|string',
        ]);

        try {
            $connection = $this->sessionManager->getConnection($session);
            $processId = $connection->executeAsync($validated['command']);

            // Store in history with null output (will be updated later)
            CommandHistory::create([
                'session_id' => $session->id,
                'user_id' => $request->user()->id,
                'command' => $validated['command'],
                'output' => null,
                'exit_code' => null,
                'duration_ms' => null,
            ]);

            $this->sessionManager->touchSession($session);

            return response()->json([
                'process_id' => $processId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getOutput(Request $request, Session $session, string $processId): JsonResponse
    {
        if (! $session->canUserAccess($request->user()->id)) {
            abort(403);
        }

        try {
            $connection = $this->sessionManager->getConnection($session);
            $output = $connection->getOutput($processId);

            return response()->json([
                'output' => $output,
                'is_running' => $connection->isProcessRunning($processId),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getStatus(Request $request, Session $session, string $processId): JsonResponse
    {
        if (! $session->canUserAccess($request->user()->id)) {
            abort(403);
        }

        try {
            $connection = $this->sessionManager->getConnection($session);

            return response()->json([
                'is_running' => $connection->isProcessRunning($processId),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function killProcess(Request $request, Session $session, string $processId): JsonResponse
    {
        if (! $session->canUserExecute($request->user()->id)) {
            abort(403, 'You do not have permission to kill processes in this session');
        }

        try {
            $connection = $this->sessionManager->getConnection($session);
            $killed = $connection->killProcess($processId);

            return response()->json([
                'killed' => $killed,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function history(Request $request, Session $session): JsonResponse
    {
        if (! $session->canUserAccess($request->user()->id)) {
            abort(403);
        }

        $history = CommandHistory::where('session_id', $session->id)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate();

        return response()->json($history);
    }
}
