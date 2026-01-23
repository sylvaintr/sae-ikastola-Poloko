import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: true,
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/facture.js',
                'resources/js/actualite.js',
                'resources/js/etiquette.js',
                'resources/js/classes.js',
                'resources/js/password-reset.js',
            ],
            refresh: true,
        }),
    ],
});
