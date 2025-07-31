import React from 'react';
import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { Session } from '@/types';

interface SessionListProps {
    sessions: Session[];
}

export default function SessionList({ sessions }: SessionListProps) {
    if (!sessions || sessions.length === 0) {
        return (
            <div className="py-12 text-center">
                <p className="text-gray-500">No active sessions</p>
            </div>
        );
    }

    return (
        <ul className="divide-y divide-gray-200">
            {sessions.map((session) => (
                <li key={session.id}>
                    <Link
                        href={route('server-manager.terminal', session.id)}
                        className="block px-4 py-4 hover:bg-gray-50 sm:px-6"
                    >
                        <div className="flex items-center justify-between">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <span className={cn(
                                        'inline-block h-2 w-2 rounded-full',
                                        session.status === 'active' ? 'bg-green-400' : 'bg-gray-400'
                                    )} />
                                </div>
                                <div className="ml-4">
                                    <div className="text-sm font-medium text-gray-900">
                                        {session.name || `Session on ${session.server.name}`}
                                    </div>
                                    <div className="text-sm text-gray-500">
                                        {session.server.username}@{session.server.host}
                                        {session.server.port !== 22 && `:${session.server.port}`}
                                    </div>
                                </div>
                            </div>
                            <div className="flex items-center">
                                <div className="text-sm text-gray-500">
                                    {session.updated_at ? (
                                        <span>Active {formatRelativeTime(session.updated_at)}</span>
                                    ) : (
                                        <span>Never used</span>
                                    )}
                                </div>
                                <svg className="ml-2 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </Link>
                </li>
            ))}
        </ul>
    );
}

function formatRelativeTime(dateString: string) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diffInSeconds < 60) return 'just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    return `${Math.floor(diffInSeconds / 86400)}d ago`;
}