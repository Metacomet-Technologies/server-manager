import React, { useEffect, useRef, forwardRef, useImperativeHandle } from 'react';
import { Terminal as XTerm } from 'xterm';
import { FitAddon } from 'xterm-addon-fit';
import { WebLinksAddon } from 'xterm-addon-web-links';
import 'xterm/css/xterm.css';

interface TerminalProps {
    sessionId: number;
    canExecute: boolean;
    onCommand: (command: string) => void;
    onResize: (cols: number, rows: number) => void;
    onClose: () => void;
}

export interface TerminalHandle {
    writeOutput: (output: string, type?: 'output' | 'input') => void;
}

const Terminal = forwardRef<TerminalHandle, TerminalProps>(
    ({ sessionId, canExecute, onCommand, onResize, onClose }, ref) => {
    const terminalRef = useRef<HTMLDivElement>(null);
    const xtermRef = useRef<XTerm | null>(null);
    const fitAddonRef = useRef<FitAddon | null>(null);

    useEffect(() => {
        // Initialize xterm.js
        const term = new XTerm({
            theme: {
                background: '#000000',
                foreground: '#00ff00',
                cursor: '#00ff00',
                cursorAccent: '#000000',
            },
            fontFamily: 'Menlo, Monaco, "Courier New", monospace',
            fontSize: 14,
            cursorBlink: true,
            convertEol: true,
            scrollback: 1000,
            disableStdin: !canExecute,
        });

        const fitAddon = new FitAddon();
        const webLinksAddon = new WebLinksAddon();

        term.loadAddon(fitAddon);
        term.loadAddon(webLinksAddon);

        xtermRef.current = term;
        fitAddonRef.current = fitAddon;

        // Open terminal in the DOM
        if (terminalRef.current) {
            term.open(terminalRef.current);
            fitAddon.fit();
        }

        // Handle resize
        const handleResize = () => {
            if (fitAddonRef.current) {
                fitAddonRef.current.fit();
                const { cols, rows } = term;
                onResize(cols, rows);
            }
        };

        window.addEventListener('resize', handleResize);

        // Handle input
        if (canExecute) {
            let inputBuffer = '';
            
            term.onData((data) => {
                if (data === '\r') { // Enter key
                    if (inputBuffer.trim()) {
                        onCommand(inputBuffer);
                        inputBuffer = '';
                    }
                    term.write('\r\n');
                } else if (data === '\x7f') { // Backspace
                    if (inputBuffer.length > 0) {
                        inputBuffer = inputBuffer.slice(0, -1);
                        term.write('\b \b');
                    }
                } else if (data === '\x03') { // Ctrl+C
                    inputBuffer = '';
                    term.write('^C\r\n$ ');
                } else {
                    inputBuffer += data;
                    term.write(data);
                }
            });
        }

        // Welcome message
        term.writeln('Welcome to Server Manager Terminal');
        term.writeln('');
        if (canExecute) {
            term.write('$ ');
        } else {
            term.writeln('[Read-only mode]');
        }

        return () => {
            window.removeEventListener('resize', handleResize);
            term.dispose();
        };
    }, [sessionId, canExecute, onCommand, onResize]);

    // Expose methods to parent component
    useImperativeHandle(ref, () => ({
        writeOutput: (output: string, type?: 'output' | 'input') => {
            if (xtermRef.current) {
                xtermRef.current.write(output);
                
                // If it's input echo, add prompt after
                if (type === 'input' && canExecute) {
                    setTimeout(() => {
                        xtermRef.current?.write('$ ');
                    }, 50);
                }
            }
        }
    }), [canExecute]);

    return (
        <div className="relative">
            <div className="absolute top-2 right-2 z-10">
                <button
                    onClick={onClose}
                    className="text-gray-400 hover:text-gray-600 focus:outline-none"
                    title="Close terminal"
                >
                    <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div
                ref={terminalRef}
                className="terminal-container"
                style={{ minHeight: '500px' }}
            />
        </div>
    );
});

Terminal.displayName = 'Terminal';

export default Terminal;