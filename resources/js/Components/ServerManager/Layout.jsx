import React from 'react';
import { Link, usePage } from '@inertiajs/react';

export default function Layout({ children }) {
    const { url } = usePage();

    const navigation = [
        { name: 'Dashboard', href: route('server-manager.dashboard'), current: url === route('server-manager.dashboard') },
        { name: 'Servers', href: route('server-manager.servers.index'), current: url.startsWith(route('server-manager.servers.index')) },
        { name: 'Sessions', href: route('server-manager.sessions.index'), current: url.startsWith(route('server-manager.sessions.index')) },
    ];

    return (
        <div className="min-h-screen bg-gray-100">
            <nav className="bg-white shadow">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <div className="flex-shrink-0 flex items-center">
                                <h1 className="text-xl font-bold text-gray-900">Server Manager</h1>
                            </div>
                            <div className="hidden sm:ml-6 sm:flex sm:space-x-8">
                                {navigation.map((item) => (
                                    <Link
                                        key={item.name}
                                        href={item.href}
                                        className={`${
                                            item.current
                                                ? 'border-indigo-500 text-gray-900'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                        } inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium`}
                                    >
                                        {item.name}
                                    </Link>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <main>{children}</main>
        </div>
    );
}