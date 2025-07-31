import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '@/Components/ServerManager/Layout';

export default function Share({ session, users, sharedUsers }) {
    const { data, setData, post, delete: destroy, processing } = useForm({
        user_id: '',
        can_execute: false,
    });
    
    const handleShare = (e) => {
        e.preventDefault();
        post(route('server-manager.sessions.share.store', session.id), {
            onSuccess: () => {
                setData({ user_id: '', can_execute: false });
            },
        });
    };
    
    const handleRevoke = (userId) => {
        if (confirm('Are you sure you want to revoke access for this user?')) {
            destroy(route('server-manager.sessions.share.destroy', [session.id, userId]));
        }
    };
    
    return (
        <Layout>
            <Head title={`Share Session - ${session.name || 'Unnamed Session'}`} />
            
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <h1 className="text-2xl font-semibold text-gray-900">
                            Share Session
                        </h1>
                        <p className="mt-1 text-sm text-gray-600">
                            {session.name || 'Unnamed Session'} on {session.server.name}
                        </p>
                    </div>
                    
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div>
                            <div className="bg-white shadow sm:rounded-lg">
                                <div className="px-4 py-5 sm:p-6">
                                    <h3 className="text-lg leading-6 font-medium text-gray-900 mb-4">
                                        Share with User
                                    </h3>
                                    
                                    <form onSubmit={handleShare}>
                                        <div className="space-y-4">
                                            <div>
                                                <label htmlFor="user_id" className="block text-sm font-medium text-gray-700">
                                                    Select User
                                                </label>
                                                <select
                                                    id="user_id"
                                                    name="user_id"
                                                    value={data.user_id}
                                                    onChange={e => setData('user_id', e.target.value)}
                                                    className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                                    required
                                                >
                                                    <option value="">Choose a user...</option>
                                                    {users.map(user => (
                                                        <option key={user.id} value={user.id}>
                                                            {user.name} ({user.email})
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>
                                            
                                            <div className="flex items-center">
                                                <input
                                                    id="can_execute"
                                                    name="can_execute"
                                                    type="checkbox"
                                                    checked={data.can_execute}
                                                    onChange={e => setData('can_execute', e.target.checked)}
                                                    className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                />
                                                <label htmlFor="can_execute" className="ml-2 block text-sm text-gray-900">
                                                    Allow command execution
                                                </label>
                                            </div>
                                            
                                            <p className="text-sm text-gray-500">
                                                Users with view-only access can see the terminal output but cannot execute commands.
                                            </p>
                                        </div>
                                        
                                        <div className="mt-5">
                                            <button
                                                type="submit"
                                                disabled={processing}
                                                className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            >
                                                {processing ? 'Sharing...' : 'Share Session'}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <div className="bg-white shadow sm:rounded-lg">
                                <div className="px-4 py-5 sm:p-6">
                                    <h3 className="text-lg leading-6 font-medium text-gray-900 mb-4">
                                        Shared With
                                    </h3>
                                    
                                    {sharedUsers.length > 0 ? (
                                        <ul className="divide-y divide-gray-200">
                                            {sharedUsers.map(share => (
                                                <li key={share.user.id} className="py-3">
                                                    <div className="flex items-center justify-between">
                                                        <div>
                                                            <p className="text-sm font-medium text-gray-900">
                                                                {share.user.name}
                                                            </p>
                                                            <p className="text-sm text-gray-500">
                                                                {share.user.email}
                                                            </p>
                                                            <p className="text-xs text-gray-500 mt-1">
                                                                {share.can_execute ? 'Can execute commands' : 'View only'}
                                                            </p>
                                                        </div>
                                                        <button
                                                            onClick={() => handleRevoke(share.user.id)}
                                                            className="text-sm text-red-600 hover:text-red-900"
                                                        >
                                                            Revoke
                                                        </button>
                                                    </div>
                                                </li>
                                            ))}
                                        </ul>
                                    ) : (
                                        <p className="text-sm text-gray-500">
                                            This session is not shared with anyone.
                                        </p>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div className="mt-6">
                        <Link
                            href={route('server-manager.sessions.index')}
                            className="text-sm text-gray-600 hover:text-gray-900"
                        >
                            ← Back to Sessions
                        </Link>
                    </div>
                </div>
            </div>
        </Layout>
    );
}