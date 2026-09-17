import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Without an explicit host, Vite on this machine binds the IPv6
        // wildcard '::' and echoes that same literal back into every asset
        // URL it generates — '::' isn't a connectable address, so every
        // css/js request 404s/connection-refuses in the actual browser.
        host: '127.0.0.1',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
