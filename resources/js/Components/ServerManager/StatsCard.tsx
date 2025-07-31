import React from 'react';
import { cn } from '@/lib/utils';

interface StatsCardProps {
    title: string;
    value: string | number;
    icon: 'server' | 'terminal' | 'plus' | 'arrow-right';
    color?: 'blue' | 'green' | 'indigo' | 'purple' | 'red' | 'yellow';
    clickable?: boolean;
}

export default function StatsCard({ 
    title, 
    value, 
    icon, 
    color = 'blue', 
    clickable = false 
}: StatsCardProps) {
    const colors = {
        blue: 'bg-blue-500',
        green: 'bg-green-500',
        indigo: 'bg-indigo-500',
        purple: 'bg-purple-500',
        red: 'bg-red-500',
        yellow: 'bg-yellow-500',
    };

    const bgColor = colors[color] || colors.blue;

    return (
        <div className={cn(
            'overflow-hidden rounded-lg bg-white shadow-sm',
            clickable && 'cursor-pointer transition-shadow hover:shadow-lg'
        )}>
            <div className="p-5">
                <div className="flex items-center">
                    <div className={cn('shrink-0 rounded-md p-3', bgColor)}>
                        {renderIcon(icon)}
                    </div>
                    <div className="ml-5 w-0 flex-1">
                        <dt className="truncate text-sm font-medium text-gray-500">
                            {title}
                        </dt>
                        <dd className="text-2xl font-semibold text-gray-900">
                            {value}
                        </dd>
                    </div>
                </div>
            </div>
        </div>
    );
}

function renderIcon(icon: StatsCardProps['icon']) {
    const iconClass = "h-6 w-6 text-white";
    
    switch (icon) {
        case 'server':
            return (
                <svg className={iconClass} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                </svg>
            );
        case 'terminal':
            return (
                <svg className={iconClass} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            );
        case 'plus':
            return (
                <svg className={iconClass} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                </svg>
            );
        case 'arrow-right':
            return (
                <svg className={iconClass} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            );
        default:
            return null;
    }
}