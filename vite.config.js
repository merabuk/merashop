import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import symfonyPlugin from 'vite-plugin-symfony';
import path from 'path';

export default defineConfig({
    base: '/build/',
    plugins: [
        vue(),
        symfonyPlugin({
            publicDir: 'public',
            refresh: true
        }),
    ],
    build: {
        outDir: 'public/build',
        manifest: true,
        rollupOptions: {
            input: {
                app: './assets/app.ts'
            },
        }
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './assets'),
            '@catalog': path.resolve(__dirname, './assets/modules/Catalog'),
            '@shared': path.resolve(__dirname, './assets/modules/Shared'),
        }
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        origin: 'https://merashop.test',
        strictPort: true,
        cors: true,
        hmr: {
            host: 'merashop.test',
            protocol: 'wss',
        },
        watch: {
            usePolling: true
        }
    }
});
