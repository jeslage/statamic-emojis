import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import statamic from '@statamic/cms/vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/addon.js',
                'resources/css/addon.css',
            ],
            publicDirectory: 'resources/dist',
        }),
        statamic(),
        tailwindcss(),
    ],
    optimizeDeps: {
        // Vue libraries must not be pre-bundled: esbuild would inline its own copy of Vue
        // instead of using the Control Panel's (window.Vue via the statamic() plugin).
        exclude: ['vue-frimousse', '@lucide/vue'],
    },
    server: {
        // Allow the Control Panel to load the dev server's modules cross-origin:
        // the playground via DDEV (https://*.ddev.site), Herd/Valet (*.test) or artisan serve (localhost).
        cors: {
            origin: [
                /^https?:\/\/.*\.ddev\.site(:\d+)?$/,
                /^https?:\/\/.*\.test(:\d+)?$/,
                /^https?:\/\/(?:(?:[^:]+\.)?localhost|127\.0\.0\.1|\[::1\])(?::\d+)?$/,
            ],
        },
    },
});
