import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '@/Components/ServerManager/Layout';

export default function Index({ servers, localServerEnabled }) {
    const { delete: destroy, processing } = useForm();
    
    const handleDelete = (server) => {
        if (confirm(`Are you sure you want to delete ${server.name}?`)) {
            destroy(route('server-manager.servers.destroy', server.id));
        }
    };
    
    const handleTestConnection = (server) => {
        fetch(route('server-manager.servers.test-connection', server.id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            alert(data.success ? 'Connection successful!' : `Connection failed: ${data.message}`);
        });
    };
    
    return (
        <Layout>
            <Head title="Servers" />
            
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center mb-6">
                        <h1 className="text-2xl font-semibold text-gray-900">
                            Servers
                        </h1>
                        <Link
                            href={route('server-manager.servers.create')}
                            className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                        >
                            Add Server
                        </Link>
                    </div>
                    
                    <div className="bg-white shadow overflow-hidden sm:rounded-md">
                        {localServerEnabled && (
                            <div className="border-b border-gray-200 bg-gray-50">
                                <Link
                                    href={route('server-manager.sessions.create', { server_id: 'local' })}
                                    className="block px-4 py-4 sm:px-6 hover:bg-gray-100"
                                >
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium text-indigo-600 truncate">
                                                Local Server
                                            </p>
                                            <p className="mt-1 text-sm text-gray-500">
                                                Access the server this application is running on
                                            </p>
                                        </div>
                                        <div className="ml-2 flex-shrink-0">
                                            <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Available
                                            </span>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        )}
                        
                        <ul className="divide-y divide-gray-200">
                            {servers.map((server) => (
                                <li key={server.id}>
                                    <div className="px-4 py-4 sm:px-6">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center">
                                                <div>
                                                    <p className="text-sm font-medium text-gray-900 truncate">
                                                        {server.name}
                                                    </p>
                                                    <p className="mt-1 text-sm text-gray-500">
                                                        {server.username}@{server.host}
                                                        {server.port !== 22 && `:${server.port}`}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <button
                                                    onClick={() => handleTestConnection(server)}
                                                    className="text-sm text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Test
                                                </button>
                                                <Link
                                                    href={route('server-manager.sessions.create', { server_id: server.id })}
                                                    className="text-sm text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Connect
                                                </Link>
                                                <Link
                                                    href={route('server-manager.servers.edit', server.id)}
                                                    className="text-sm text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    onClick={() => handleDelete(server)}
                                                    disabled={processing}
                                                    className="text-sm text-red-600 hover:text-red-900"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                        
                        {servers.length === 0 && (
                            <div className="text-center py-12">
                                <p className="text-gray-500">No servers configured yet.</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </Layout>
    );
}