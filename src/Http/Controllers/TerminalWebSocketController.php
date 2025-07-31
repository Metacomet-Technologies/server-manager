<?php

namespace Metacomet\ServerManager\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Metacomet\ServerManager\Models\Session;
use Metacomet\ServerManager\Services\TerminalService;

class TerminalWebSocketController extends Controller
{
    public function __construct(private TerminalService $terminalService) {}

    public function execute(Request $request, Session $session): JsonResponse
    {
        if (! $session->canUserExecute($request->user()->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'command' => 'required|string',
        ]);

        // Execute command asynchronously
        $this->terminalService->executeCommand(
            $session,
            $validated['command'],
            $request->user()->id
        );

        return response()->json(['status' => 'executing']);
    }

    public function resize(Request $request, Session $session): JsonResponse
    {
        if (! $session->canUserAccess($request->user()->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'cols' => 'required|integer|min:10|max:500',
            'rows' => 'required|integer|min:5|max:200',
        ]);

        $this->terminalService->resize($session, $validated['cols'], $validated['rows']);

        return response()->json(['status' => 'resized']);
    }
}
