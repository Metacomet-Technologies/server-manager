<?php

namespace Metacomet\ServerManager\Http\Controllers\Web;

use Inertia\Inertia;
use Metacomet\ServerManager\Models\Server;
use Metacomet\ServerManager\Models\Session;

class DashboardController
{
    public function index(): \Inertia\Response
    {
        $userId = auth()->id();

        /** @var array<string, int> $stats */
        $stats = [
            'servers' => Server::where('user_id', $userId)->count(),
            'activeSessions' => Session::where('user_id', $userId)
                ->where('is_active', true)
                ->count(),
        ];

        $recentSessions = Session::with('server')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->latest('last_activity_at')
            ->take(5)
            ->get();

        return Inertia::render('ServerManager/Dashboard', [
            'stats' => $stats,
            'recentSessions' => $recentSessions,
        ]);
    }
}
