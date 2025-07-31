import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '@/Components/ServerManager/Layout';
import { PageProps, Server } from '@/types';

interface IndexProps extends PageProps {
    servers: Server[];
    localServerEnabled: boolean;
}

export default function Index({ servers, localServerEnabled }: IndexProps) {
    const { delete: destroy, processing } = useForm();
    
    const handleDelete = (server: Server) => {
        if (confirm(`Are you sure you want to delete ${server.name}?`)) {
            destroy(route('server-manager.servers.destroy', server.id));
        }
    };
    
    const handleTestConnection = async (server: Server) => {
        const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
        
        if (!csrfToken) {
            alert('CSRF token not found');
            return;
        }
        
        try {
            const response = await fetch(route('server-manager.servers.test-connection', server.id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });
            
            const data = await response.json();
            alert(data.success ? 'Connection successful!' : `Connection failed: ${data.message}`);
        } catch (error) {
            alert('Failed to test connection');
        }
    };
    
    return (
        <Layout>
            <Head title="Servers" />
            
            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <h1 className="text-2xl font-semibold text-gray-900">
                            Servers
                        </h1>
                        <Link
                            href={route('server-manager.servers.create')}
                            className="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-xs hover:bg-indigo-700"
                        >
                            Add Server
                        </Link>
                    </div>
                    
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-md">
                        {localServerEnabled && (
                            <div className="border-b border-gray-200 bg-gray-50">
                                <Link
                                    href={route('server-manager.sessions.create', { server_id: 'local' })}
                                    className="flex items-center px-6 py-4 hover:bg-gray-100"
                                >
                                    <div className="flex min-w-0 flex-1 items-center">
                                        <div className="shrink-0">
                                            <svg className="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div className="min-w-0 flex-1 px-4">
                                            <div>
                                                <p className="text-sm font-medium text-gray-900">
                                                    Local Server
                                                </p>
                                                <p className="text-sm text-gray-500">
                                                    Access the server hosting this application
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <svg className="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                                        </svg>
                                    </div>
                                </Link>
                            </div>
                        )}
                        
                        <ul className="divide-y divide-gray-200">
                            {servers.map((server) => (
                                <li key={server.id}>
                                    <div className="flex items-center px-4 py-4 sm:px-6">
                                        <div className="flex min-w-0 flex-1 items-center">
                                            <div className="shrink-0">
                                                <span className={`inline-block h-2 w-2 rounded-full ${
                                                    server.is_local ? 'bg-blue-400' : 'bg-green-400'
                                                }`} />
                                            </div>
                                            <div className="min-w-0 flex-1 px-4 md:grid md:grid-cols-2 md:gap-4">
                                                <div>
                                                    <p className="text-sm font-medium text-indigo-600">
                                                        {server.name}
                                                    </p>
                                                    <p className="mt-1 flex items-center text-sm text-gray-500">
                                                        {server.username}@{server.host}:{server.port}
                                                    </p>
                                                </div>
                                                <div className="hidden md:block">
                                                    <div>
                                                        <p className="text-sm text-gray-900">
                                                            Authentication: {server.auth_type === 'password' ? 'Password' : 'SSH Key'}
                                                        </p>
                                                        <p className="mt-1 text-sm text-gray-500">
                                                            Added {new Date(server.created_at).toLocaleDateString()}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <button
                                                onClick={() => handleTestConnection(server)}
                                                className="inline-flex items-center rounded bg-gray-100 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200"
                                            >
                                                Test
                                            </button>
                                            <Link
                                                href={route('server-manager.sessions.create', { server_id: server.id })}
                                                className="inline-flex items-center rounded bg-green-100 px-2.5 py-1.5 text-xs font-medium text-green-700 hover:bg-green-200"
                                            >
                                                Connect
                                            </Link>
                                            <Link
                                                href={route('server-manager.servers.edit', server.id)}
                                                className="inline-flex items-center rounded bg-indigo-100 px-2.5 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-200"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                onClick={() => handleDelete(server)}
                                                disabled={processing}
                                                className="inline-flex items-center rounded bg-red-100 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-200"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                        
                        {servers.length === 0 && (
                            <div className="px-6 py-12 text-center">
                                <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                </svg>
                                <h3 className="mt-2 text-sm font-medium text-gray-900">No servers</h3>
                                <p className="mt-1 text-sm text-gray-500">Get started by adding a new server.</p>
                                <div className="mt-6">
                                    <Link
                                        href={route('server-manager.servers.create')}
                                        className="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-xs hover:bg-indigo-700"
                                    >
                                        Add Server
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