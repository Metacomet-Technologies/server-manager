import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '@/Components/ServerManager/Layout';
import { PageProps, Session } from '@/types';

interface IndexProps extends PageProps {
    sessions: Session[];
}

export default function Index({ sessions }: IndexProps) {
    const { delete: destroy } = useForm();
    
    const handleDelete = (session: Session) => {
        if (confirm('Are you sure you want to end this session?')) {
            destroy(route('server-manager.sessions.destroy', session.id));
        }
    };
    
    return (
        <Layout>
            <Head title="Sessions" />
            
            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <h1 className="text-2xl font-semibold text-gray-900">
                            Sessions
                        </h1>
                        <Link
                            href={route('server-manager.servers.index')}
                            className="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-xs hover:bg-indigo-700"
                        >
                            Connect to Server
                        </Link>
                    </div>
                    
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-md">
                        <ul className="divide-y divide-gray-200">
                            {sessions.map((session) => (
                                <li key={session.id}>
                                    <div className="px-4 py-4 sm:px-6">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center">
                                                <div className="shrink-0">
                                                    <span className={`inline-block h-3 w-3 rounded-full ${
                                                        session.status === 'active' ? 'bg-green-400' : 'bg-gray-400'
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
                                                    <div className="mt-1 text-xs text-gray-400">
                                                        Created {new Date(session.created_at).toLocaleString()}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Link
                                                    href={route('server-manager.terminal', session.id)}
                                                    className="inline-flex items-center rounded bg-green-100 px-2.5 py-1.5 text-xs font-medium text-green-700 hover:bg-green-200"
                                                >
                                                    Open
                                                </Link>
                                                <Link
                                                    href={route('server-manager.sessions.share', session.id)}
                                                    className="inline-flex items-center rounded bg-blue-100 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-200"
                                                >
                                                    Share
                                                </Link>
                                                <button
                                                    onClick={() => handleDelete(session)}
                                                    className="inline-flex items-center rounded bg-red-100 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-200"
                                                >
                                                    End
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                        
                        {sessions.length === 0 && (
                            <div className="px-6 py-12 text-center">
                                <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <h3 className="mt-2 text-sm font-medium text-gray-900">No active sessions</h3>
                                <p className="mt-1 text-sm text-gray-500">Get started by connecting to a server.</p>
                                <div className="mt-6">
                                    <Link
                                        href={route('server-manager.servers.index')}
                                        className="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-xs hover:bg-indigo-700"
                                    >
                                        Connect to Server
                                    </Link>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </Layout>
    );
}