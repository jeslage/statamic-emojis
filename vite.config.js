import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import statamic from "@statamic/cms/vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/js/addon.js", "resources/css/addon.css"],
            publicDirectory: "resources/dist",
        }),
        statamic(),
        tailwindcss(),
    ],
    optimizeDeps: {
        // Not pre-bundled: esbuild would inline its own Vue instead of the Control Panel's (window.Vue).
        exclude: ["vue-frimousse", "@lucide/vue"],
    },
    server: {
        // Lets the Control Panel load the dev server's modules cross-origin (DDEV, Herd/Valet, artisan serve)
        cors: {
            origin: [
                /^https?:\/\/.*\.ddev\.site(:\d+)?$/,
                /^https?:\/\/.*\.test(:\d+)?$/,
                /^https?:\/\/(?:(?:[^:]+\.)?localhost|127\.0\.0\.1|\[::1\])(?::\d+)?$/,
            ],
        },
    },
});
