import React, { useRef, useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import { useEcho } from '@laravel/echo-react';
import axios from 'axios';
import { cn } from '@/lib/utils';
import Layout from '@/Components/ServerManager/Layout';
import Terminal, { TerminalHandle } from '@/Components/ServerManager/Terminal';
import { Session, PageProps } from '@/types';

interface TerminalPageProps extends PageProps {
    session: Session;
    canExecute: boolean;
    websocket: {
        key: string;
        cluster: string;
        port: number;
        host: string;
        scheme: string;
    };
}

interface TerminalOutput {
    output: string;
    type: 'output' | 'input';
}

export default function TerminalPage({ session, canExecute }: TerminalPageProps) {
    const terminalRef = useRef<TerminalHandle>(null);
    const [connectionError, setConnectionError] = useState<string | null>(null);
    const [isConnected, setIsConnected] = useState(false);
    
    // Use the Laravel Echo React hook
    const { leaveChannel } = useEcho(
        `private-server-manager.session.${session.id}`,
        '.terminal.output',
        (event: TerminalOutput) => {
            terminalRef.current?.writeOutput(event.output, event.type);
        }
    );

    useEffect(() => {
        // Set connected status when component mounts
        setIsConnected(true);
        
        // Handle connection errors
        window.Echo.connector.channels[`private-server-manager.session.${session.id}`]?.subscription?.bind('pusher:subscription_error', (error: any) => {
            console.error('WebSocket error:', error);
            setConnectionError('Connection error. Please refresh the page.');
            setIsConnected(false);
        });

        return () => {
            // Cleanup is handled by useEcho hook
            setIsConnected(false);
        };
    }, [session.id]);

    const handleCommand = async (command: string) => {
        try {
            await axios.post(route('api.server-manager.terminal.execute', session.id), {
                command
            });
        } catch (error) {
            console.error('Failed to execute command:', error);
        }
    };

    const handleResize = async (cols: number, rows: number) => {
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
        leaveChannel();
        router.visit(route('server-manager.sessions.index'));
    };

    return (
        <Layout>
            <Head title={`Terminal - ${session.name || session.server.name}`} />
            
            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white shadow-sm">
                        <div className="border-b border-gray-200 px-4 py-5 sm:px-6">
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
                                    <span className={cn(
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        isConnected ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    )}>
                                        <span className={cn(
                                            'mr-1 h-2 w-2 rounded-full',
                                            isConnected ? 'bg-green-400' : 'bg-red-400'
                                        )} />
                                        {isConnected ? 'Connected' : 'Disconnected'}
                                    </span>
                                    {!canExecute && (
                                        <span className="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                            Read Only
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>
                        
                        <div className="p-4">
                            {connectionError && (
                                <div className="mb-4 rounded-md bg-red-50 p-4">
                                    <div className="flex">
                                        <div className="shrink-0">
                                            <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                                            </svg>
                                        </div>
                                        <div className="ml-3">
                                            <p className="text-sm text-red-800">{connectionError}</p>
                                        </div>
                                    </div>
                                </div>
                            )}
                            
                            <Terminal
                                ref={terminalRef}
                                sessionId={session.id}
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