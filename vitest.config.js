import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';

const stub = (file) => fileURLToPath(new URL(`./tests/js/stubs/${file}`, import.meta.url));

export default defineConfig({
    plugins: [vue()],
    resolve: {
        // In the Control Panel these come from Statamic at runtime (window globals), so tests use stand-ins
        alias: [
            { find: /^@statamic\/cms\/ui$/, replacement: stub('statamic-ui.js') },
            { find: /^@statamic\/cms$/, replacement: stub('statamic-cms.js') },
        ],
    },
    test: {
        environment: 'jsdom',
        include: ['tests/js/**/*.test.js'],
        setupFiles: ['tests/js/setup.js'],
    },
});
