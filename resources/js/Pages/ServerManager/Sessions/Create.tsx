import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import Layout from '@/Components/ServerManager/Layout';
import { PageProps, Server } from '@/types';

interface CreateProps extends PageProps {
    server?: Server;
    server_id?: string | number;
}

export default function Create({ server, server_id }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        server_id: server_id || '',
        name: '',
    });
    
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('server-manager.sessions.store'));
    };
    
    return (
        <Layout>
            <Head title="Create Session" />
            
            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="md:grid md:grid-cols-3 md:gap-6">
                        <div className="md:col-span-1">
                            <div className="px-4 sm:px-0">
                                <h3 className="text-lg font-medium leading-6 text-gray-900">
                                    Create Session
                                </h3>
                                <p className="mt-1 text-sm text-gray-600">
                                    Start a new terminal session with {server ? server.name : 'the selected server'}.
                                </p>
                            </div>
                        </div>
                        
                        <div className="mt-5 md:col-span-2 md:mt-0">
                            <form onSubmit={handleSubmit}>
                                <div className="overflow-hidden shadow-sm sm:rounded-md">
                                    <div className="space-y-6 bg-white px-4 py-5 sm:p-6">
                                        {server && (
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">
                                                    Server
                                                </label>
                                                <div className="mt-1 text-sm text-gray-900">
                                                    {server.name} ({server.username}@{server.host}:{server.port})
                                                </div>
                                            </div>
                                        )}
                                        
                                        <div>
                                            <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                                Session Name (optional)
                                            </label>
                                            <input
                                                type="text"
                                                name="name"
                                                id="name"
                                                value={data.name}
                                                onChange={e => setData('name', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-xs focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="My work session"
                                            />
                                            {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                                        </div>
                                    </div>
                                    
                                    <div className="bg-gray-50 px-4 py-3 text-right sm:px-6">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-xs hover:bg-indigo-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        >
                                            {processing ? 'Creating...' : 'Create Session'}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
    );
}