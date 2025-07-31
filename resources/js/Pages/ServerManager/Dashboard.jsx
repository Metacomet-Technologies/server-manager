import React from 'react';
import { Head, Link } from '@inertiajs/react';
import Layout from '@/Components/ServerManager/Layout';
import StatsCard from '@/Components/ServerManager/StatsCard';
import SessionList from '@/Components/ServerManager/SessionList';

export default function Dashboard({ stats, recentSessions }) {
    return (
        <Layout>
            <Head title="Dashboard" />
            
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h1 className="text-2xl font-semibold text-gray-900 mb-6">
                        Server Manager Dashboard
                    </h1>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <StatsCard
                            title="Total Servers"
                            value={stats.servers}
                            icon="server"
                            color="blue"
                        />
                        <StatsCard
                            title="Active Sessions"
                            value={stats.activeSessions}
                            icon="terminal"
                            color="green"
                        />
                        <Link href={route('server-manager.servers.create')}>
                            <StatsCard
                                title="Add Server"
                                value="+"
                                icon="plus"
                                color="indigo"
                                clickable
                            />
                        </Link>
                        <Link href={route('server-manager.sessions.index')}>
                            <StatsCard
                                title="View All Sessions"
                                value="→"
                                icon="arrow-right"
                                color="purple"
                                clickable
                            />
                        </Link>
                    </div>
                    
                    <div className="bg-white shadow rounded-lg">
                        <div className="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h2 className="text-lg font-medium text-gray-900">
                                Recent Sessions
                            </h2>
                        </div>
                        <SessionList sessions={recentSessions} />
                    </div>
                </div>
            </div>
        </Layout>
    );
}