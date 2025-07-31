export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    created_at: string;
    updated_at: string;
}

export interface Server {
    id: number;
    user_id: number;
    name: string;
    host: string;
    port: number;
    username: string;
    auth_type: 'password' | 'key';
    password?: string;
    private_key?: string;
    is_local: boolean;
    created_at: string;
    updated_at: string;
}

export interface Session {
    id: number;
    user_id: number;
    server_id: number;
    name?: string;
    status: 'active' | 'inactive' | 'disconnected';
    created_at: string;
    updated_at: string;
    server: Server;
    shared_users?: SharedUser[];
}

export interface SharedUser {
    id: number;
    user_id: number;
    permission: 'view' | 'execute';
    user: User;
}

export interface Command {
    id: number;
    session_id: number;
    user_id: number;
    command: string;
    output?: string;
    executed_at: string;
    user: User;
}

export interface PageProps<T extends Record<string, unknown> = Record<string, unknown>> {
    auth: {
        user: User;
    };
    flash?: {
        success?: string;
        error?: string;
    };
    [key: string]: any;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: {
        url?: string;
        label: string;
        active: boolean;
    }[];
}

declare module '@inertiajs/react' {
    export function usePage<T = PageProps>(): {
        props: T;
        component: string;
        url: string;
        version: string;
    };
}