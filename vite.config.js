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
        // The app is loaded from the Laragon virtual host
        // (http://sms-broadcast-system.me), a different origin than the
        // Vite dev server (127.0.0.1:5173) — without this, Vite's default
        // same-origin CORS policy blocks the browser from loading
        // @vite/client and the HMR websocket.
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
