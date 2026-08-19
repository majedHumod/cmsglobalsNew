import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Tremor-compatible admin brand (orange / gray / white / black)
                tremor: {
                    brand: {
                        faint: '#fff7ed',
                        muted: '#ffedd5',
                        subtle: '#fdba74',
                        DEFAULT: '#f97316',
                        emphasis: '#ea580c',
                        inverted: '#ffffff',
                    },
                    background: {
                        muted: '#f9fafb',
                        subtle: '#f3f4f6',
                        DEFAULT: '#ffffff',
                        emphasis: '#374151',
                    },
                    border: {
                        DEFAULT: '#e5e7eb',
                    },
                    ring: {
                        DEFAULT: '#e5e7eb',
                    },
                    content: {
                        subtle: '#9ca3af',
                        DEFAULT: '#6b7280',
                        emphasis: '#374151',
                        strong: '#111827',
                        inverted: '#ffffff',
                    },
                },
            },
            boxShadow: {
                'tremor-input': '0 1px 2px 0 rgb(0 0 0 / 0.05)',
                'tremor-card': '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
                'tremor-dropdown': '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
            },
            borderRadius: {
                'tremor-small': '0.375rem',
                'tremor-default': '0.5rem',
                'tremor-full': '9999px',
            },
            fontSize: {
                'tremor-label': ['0.75rem', { lineHeight: '1rem' }],
                'tremor-default': ['0.875rem', { lineHeight: '1.25rem' }],
                'tremor-title': ['1.125rem', { lineHeight: '1.75rem' }],
                'tremor-metric': ['1.875rem', { lineHeight: '2.25rem' }],
            },
        },
    },

    plugins: [forms, typography],
};
