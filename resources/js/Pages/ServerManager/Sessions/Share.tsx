import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '@/Components/ServerManager/Layout';
import { PageProps, Session, User, SharedUser } from '@/types';

interface ShareProps extends PageProps {
    session: Session;
    users: User[];
    sharedUsers: SharedUser[];
}

export default function Share({ session, users, sharedUsers }: ShareProps) {
    const { data, setData, post, delete: destroy, processing } = useForm({
        user_id: '',
        can_execute: false,
    });
    
    const handleShare = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('server-manager.sessions.share.store', session.id), {
            onSuccess: () => {
                setData({ user_id: '', can_execute: false });
            },
        });
    };
    
    const handleRevoke = (userId: number) => {
        if (confirm('Are you sure you want to revoke access for this user?')) {
            destroy(route('server-manager.sessions.share.destroy', [session.id, userId]));
        }
    };
    
    return (
        <Layout>
            <Head title={`Share Session - ${session.name || 'Unnamed Session'}`} />
            
            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
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
                            <div className="bg-white shadow-sm sm:rounded-lg">
                                <div className="px-4 py-5 sm:p-6">
                                    <h3 className="mb-4 text-lg font-medium leading-6 text-gray-900">
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
                                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-xs focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    required
                                                >
                                                    <option value="">Choose a user...</option>
                                                    {users.filter(user => 
                                                        !sharedUsers.some(share => share.user_id === user.id)
                                                    ).map(user => (
                                                        <option key={user.id} value={user.id}>
                                                            {user.name} ({user.email})
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>
                                            
                                            <div>
                                                <label className="flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        name="can_execute"
                                                        checked={data.can_execute}
                                                        onChange={e => setData('can_execute', e.target.checked as any)}
                                                        className="rounded border-gray-300 text-indigo-600 shadow-xs focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    />
                                                    <span className="ml-2 text-sm text-gray-700">
                                                        Allow command execution
                                                    </span>
                                                </label>
                                                <p className="mt-1 text-xs text-gray-500">
                                                    If unchecked, user will have read-only access
                                                </p>
                                            </div>
                                            
                                            <div>
                                                <button
                                                    type="submit"
                                                    disabled={processing || !data.user_id}
                                                    className="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-xs hover:bg-indigo-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                                >
                                                    Share Session
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <div className="bg-white shadow-sm sm:rounded-lg">
                                <div className="px-4 py-5 sm:p-6">
                                    <h3 className="mb-4 text-lg font-medium leading-6 text-gray-900">
                                        Shared With
                                    </h3>
                                    
                                    {sharedUsers.length > 0 ? (
                                        <ul className="divide-y divide-gray-200">
                                            {sharedUsers.map(share => (
                                                <li key={share.id} className="py-3">
                                                    <div className="flex items-center justify-between">
                                                        <div>
                                                            <p className="text-sm font-medium text-gray-900">
                                                                {share.user.name}
                                                            </p>
                                                            <p className="text-sm text-gray-500">
                                                                {share.user.email}
                                                            </p>
                                                            <p className="text-xs text-gray-400">
                                                                {share.permission === 'execute' ? 'Can execute commands' : 'Read-only access'}
                                                            </p>
                                                        </div>
                                                        <button
                                                            onClick={() => handleRevoke(share.user_id)}
                                                            className="inline-flex items-center rounded bg-red-100 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-200"
                                                        >
                                                            Revoke
                                                        </button>
                                                    </div>
                                                </li>
                                            ))}
                                        </ul>
                                    ) : (
                                        <p className="text-sm text-gray-500">
                                            This session is not shared with anyone yet.
                                        </p>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div className="mt-6">
                        <Link
                            href={route('server-manager.sessions.index')}
                            className="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-xs hover:bg-gray-50"
                        >
                            Back to Sessions
                        </Link>
                    </div>
                </div>
            </div>
        </Layout>
    );
}