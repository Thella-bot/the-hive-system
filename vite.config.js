import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    optimizeDeps: {
        include: ['@heroicons/vue/24/outline'],
    },
    build: {
        rollupOptions: {
            external: ['fsevents'],
            output: {
                manualChunks: {
                    'vendor-vue': ['vue', 'vue-router', 'pinia'],
                    'vendor-chart': ['chart.js', 'vue-chartjs'],
                    'vendor-calendar': ['@fullcalendar/core', '@fullcalendar/daygrid', '@fullcalendar/timegrid', '@fullcalendar/interaction', '@fullcalendar/vue3'],
                    'vendor-inertia': ['@inertiajs/vue3', 'laravel-vite-plugin'],
                    'vendor-icons': ['@heroicons/vue'],
                },
            },
        },
        cssCodeSplit: true,
        sourcemap: false,
    },
    server: {
        host: 'localhost',
        port: 5173,
        cors: {
            origin: '*',
            methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            allowedHeaders: ['Content-Type', 'Authorization', 'X-XSRF-TOKEN'],
            credentials: true,
        },
        proxy: {
            '/api': {
                target: 'http://192.168.1.61:8000',
                changeOrigin: true,
            },
            '/login': {
                target: 'http://192.168.1.61:8000',
                changeOrigin: true,
            },
            '/logout': {
                target: 'http://192.168.1.61:8000',
                changeOrigin: true,
            },
            '/sanctum': {
                target: 'http://192.168.1.61:8000',
                changeOrigin: true,
            },
            '/register': {
                target: 'http://192.168.1.61:8000',
                changeOrigin: true,
            },
        },
        watch: {
            ignored: [
                "**/.idea/**",
                "**/storage/**",
                "**/bootstrap/cache/**",
            ]
        }
    }
});
