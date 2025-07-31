import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * Combine class names and apply Tailwind CSS JIT.
 *
 * @param inputs The class names to combine.
 */
export function cn(...inputs: ClassValue[]): string {
    return twMerge(clsx(inputs));
}