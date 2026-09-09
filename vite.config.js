// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    // Port 5174, not Vite's default 5173: another project on this machine
    // holds 5173, and because strictPort refuses to fall back, `npm run dev`
    // would die on startup while still leaving a public/hot file behind. The
    // app then reads that stale file and pulls its CSS from the *other*
    // project's dev server, which looks like the styles randomly broke.
    server: {
        host: '127.0.0.1',
        port: 5174,
        strictPort: true,
        origin: 'http://localhost:5174',
        // Vite 6 tightened the dev server's CORS default. localhost and
        // 127.0.0.1 on the app's usual port still work without this, but any
        // other local origin — a second port, a LAN address used to test on a
        // phone — is refused, and the page then loads with no scripts at all
        // rather than any visible error. Widening it to the loopback names
        // keeps those cases working. Dev-only: built assets are same-origin.
        cors: {
            origin: [/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/],
        },
        hmr: {
            host: 'localhost',
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/providers.css',
                'resources/css/provider-profile.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
