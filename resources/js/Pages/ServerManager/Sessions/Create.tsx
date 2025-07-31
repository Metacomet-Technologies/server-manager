import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import Layout from '@/Components/ServerManager/Layout';

export default function Create({ server, server_id }) {
    const { data, setData, post, processing, errors } = useForm({
        server_id: server_id || '',
        name: '',
    });
    
    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('server-manager.sessions.store'));
    };
    
    return (
        <Layout>
            <Head title="Create Session" />
            
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                        
                        <div className="mt-5 md:mt-0 md:col-span-2">
                            <form onSubmit={handleSubmit}>
                                <div className="shadow sm:rounded-md sm:overflow-hidden">
                                    <div className="px-4 py-5 bg-white space-y-6 sm:p-6">
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
                                                Session Name (Optional)
                                            </label>
                                            <input
                                                type="text"
                                                name="name"
                                                id="name"
                                                value={data.name}
                                                onChange={e => setData('name', e.target.value)}
                                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                placeholder="e.g., Deployment Session"
                                            />
                                            {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                                            <p className="mt-1 text-sm text-gray-500">
                                                Leave empty to use default naming.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div className="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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