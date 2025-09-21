import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        // Optimize build for production
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true,
            },
        },
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['alpinejs'],
                },
            },
        },
        // Enable CSS code splitting
        cssCodeSplit: true,
        // Optimize chunk size
        chunkSizeWarningLimit: 1000,
    },
    css: {
        devSourcemap: false,
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            overlay: false,
            port: 5173,
        },
    },
});