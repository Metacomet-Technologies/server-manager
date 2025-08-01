import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

// Extend window interface for server manager config
declare global {
    interface Window {
        serverManagerConfig?: {
            websocketEnabled: boolean;
            prefix: string;
        };
    }
}

// Only initialize WebSocket support if configured
if (import.meta.env.VITE_REVERB_APP_KEY) {
    // Dynamically import WebSocket dependencies only when needed
    import('pusher-js').then(({ default: Pusher }) => {
        window.Pusher = Pusher;
        
        import('laravel-echo').then(({ default: Echo }) => {
            import('@laravel/echo-react').then(({ configureEcho }) => {
                // Configure Echo for Reverb
                configureEcho({
                    broadcaster: 'reverb',
                    key: import.meta.env.VITE_REVERB_APP_KEY,
                    wsHost: import.meta.env.VITE_REVERB_HOST,
                    wsPort: parseInt(import.meta.env.VITE_REVERB_PORT || '80'),
                    wssPort: parseInt(import.meta.env.VITE_REVERB_PORT || '443'),
                    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
                    enabledTransports: ['ws', 'wss'],
                });

                // Also create global Echo instance for backward compatibility
                window.Echo = new Echo({
                    broadcaster: 'reverb',
                    key: import.meta.env.VITE_REVERB_APP_KEY,
                    wsHost: import.meta.env.VITE_REVERB_HOST,
                    wsPort: parseInt(import.meta.env.VITE_REVERB_PORT || '80'),
                    wssPort: parseInt(import.meta.env.VITE_REVERB_PORT || '443'),
                    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
                    enabledTransports: ['ws', 'wss'],
                });
            });
        });
    });
}

createInertiaApp({
    title: (title) => `${title} - Server Manager`,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true });
        const page = pages[`./Pages/${name}.tsx`];
        if (!page) {
            throw new Error(`Page not found: ./Pages/${name}.tsx`);
        }
        return page;
    },
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
});