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
