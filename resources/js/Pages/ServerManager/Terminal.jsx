import React, { useEffect, useRef, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import Layout from '@/Components/ServerManager/Layout';
import Terminal from '@/Components/ServerManager/Terminal';

export default function TerminalPage({ session, canExecute, websocket }) {
    const [isConnected, setIsConnected] = useState(false);
    const channelRef = useRef(null);

    useEffect(() => {
        // Subscribe to the session channel
        const channelName = `${window.Echo.options.namespace || ''}.server-manager.session.${session.id}`;
        channelRef.current = window.Echo.private(channelName);

        channelRef.current
            .subscribed(() => {
                setIsConnected(true);
            })
            .listen('.terminal.output', (event) => {
                // Terminal component will handle the output
            })
            .error((error) => {
                console.error('WebSocket error:', error);
                setIsConnected(false);
            });

        return () => {
            if (channelRef.current) {
                window.Echo.leaveChannel(channelName);
            }
        };
    }, [session.id]);

    const handleCommand = async (command) => {
        try {
            await axios.post(route('api.server-manager.terminal.execute', session.id), {
                command
            });
        } catch (error) {
            console.error('Failed to execute command:', error);
        }
    };

    const handleResize = async (cols, rows) => {
        try {
            await axios.post(route('api.server-manager.terminal.resize', session.id), {
                cols,
                rows
            });
        } catch (error) {
            console.error('Failed to resize terminal:', error);
        }
    };

    const handleClose = () => {
        router.visit(route('server-manager.sessions.index'));
    };

    return (
        <Layout>
            <Head title={`Terminal - ${session.name || session.server.name}`} />
            
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="bg-white shadow rounded-lg">
                        <div className="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h2 className="text-lg font-medium text-gray-900">
                                        {session.name || `Session on ${session.server.name}`}
                                    </h2>
                                    <p className="mt-1 text-sm text-gray-500">
                                        {session.server.username}@{session.server.host}
                                        {session.server.port !== 22 && `:${session.server.port}`}
                                    </p>
                                </div>
                                <div className="flex items-center space-x-3">
                                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                        isConnected ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    }`}>
                                        <span className={`mr-1 h-2 w-2 rounded-full ${
                                            isConnected ? 'bg-green-400' : 'bg-red-400'
                                        }`} />
                                        {isConnected ? 'Connected' : 'Disconnected'}
                                    </span>
                                    {!canExecute && (
                                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Read Only
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>
                        
                        <div className="p-4">
                            <Terminal
                                sessionId={session.id}
                                channel={channelRef.current}
                                canExecute={canExecute}
                                onCommand={handleCommand}
                                onResize={handleResize}
                                onClose={handleClose}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
    );
}