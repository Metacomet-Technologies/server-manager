import React from 'react';
import { Head, Link } from '@inertiajs/react';
import Layout from '@/Components/ServerManager/Layout';
import StatsCard from '@/Components/ServerManager/StatsCard';
import SessionList from '@/Components/ServerManager/SessionList';
import { Session, PageProps } from '@/types';

interface DashboardProps extends PageProps {
    stats: {
        servers: number;
        activeSessions: number;
    };
    recentSessions: Session[];
}

export default function Dashboard({ stats, recentSessions }: DashboardProps) {
    return (
        <Layout>
            <Head title="Dashboard" />
            
            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h1 className="mb-6 text-2xl font-semibold text-gray-900">
                        Server Manager Dashboard
                    </h1>
                    
                    <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
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
                    
                    <div className="rounded-lg bg-white shadow">
                        <div className="border-b border-gray-200 px-4 py-5 sm:px-6">
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