import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.jsx'],
                refresh: true,
            }),
            react(),
        ],
        server: {
            host: env.VITE_DEV_SERVER_HOST || '0.0.0.0',
            port: Number(env.VITE_DEV_SERVER_PORT || 5173),
            strictPort: true,
        },
    };
});
