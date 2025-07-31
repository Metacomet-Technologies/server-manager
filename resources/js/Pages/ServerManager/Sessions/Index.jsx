import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '@/Components/ServerManager/Layout';

export default function Index({ sessions }) {
    const { delete: destroy } = useForm();
    
    const handleDelete = (session) => {
        if (confirm('Are you sure you want to end this session?')) {
            destroy(route('server-manager.sessions.destroy', session.id));
        }
    };
    
    return (
        <Layout>
            <Head title="Sessions" />
            
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center mb-6">
                        <h1 className="text-2xl font-semibold text-gray-900">
                            Sessions
                        </h1>
                        <Link
                            href={route('server-manager.servers.index')}
                            className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                        >
                            Connect to Server
                        </Link>
                    </div>
                    
                    <div className="bg-white shadow overflow-hidden sm:rounded-md">
                        <ul className="divide-y divide-gray-200">
                            {sessions.map((session) => (
                                <li key={session.id}>
                                    <div className="px-4 py-4 sm:px-6">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center">
                                                <div className="flex-shrink-0">
                                                    <span className={`inline-block h-3 w-3 rounded-full ${
                                                        session.is_active ? 'bg-green-400' : 'bg-gray-400'
                                                    }`} />
                                                </div>
                                                <div className="ml-4">
                                                    <div className="text-sm font-medium text-gray-900">
                                                        {session.name || `Session on ${session.server.name}`}
                                                    </div>
                                                    <div className="text-sm text-gray-500">
                                                        {session.server.username}@{session.server.host}
                                                        {session.server.port !== 22 && `:${session.server.port}`}
                                                    </div>
                                                    <div className="mt-1 text-xs text-gray-500">
                                                        Created {new Date(session.created_at).toLocaleString()}
                                                        {session.last_activity_at && (
                                                            <span> • Last active {new Date(session.last_activity_at).toLocaleString()}</span>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                {session.shared_users && session.shared_users.length > 0 && (
                                                    <span className="text-xs text-gray-500">
                                                        Shared with {session.shared_users.length} user(s)
                                                    </span>
                                                )}
                                                <Link
                                                    href={route('server-manager.terminal', session.id)}
                                                    className="text-sm text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Open Terminal
                                                </Link>
                                                <Link
                                                    href={route('server-manager.sessions.share', session.id)}
                                                    className="text-sm text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Share
                                                </Link>
                                                <button
                                                    onClick={() => handleDelete(session)}
                                                    className="text-sm text-red-600 hover:text-red-900"
                                                >
                                                    End Session
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                        
                        {sessions.length === 0 && (
                            <div className="text-center py-12">
                                <p className="text-gray-500">No active sessions.</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </Layout>
    );
}