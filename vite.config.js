import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/report-summary.js',
                'resources/js/focus-area/index.js',
                'resources/css/report-summary.css',
                'resources/css/cobit5/document.css',
                'resources/js/cobit5/document-editor.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
