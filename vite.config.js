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
                admin: './assets/admin.ts',
                shop: './assets/shop.ts'
            },
        }
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './assets'),
            '@catalog': path.resolve(__dirname, './assets/modules/Catalog'),
            '@identity-access': path.resolve(__dirname, './assets/modules/IdentityAccess'),
            '@shared': path.resolve(__dirname, './assets/modules/Shared'),
            'vue': 'vue/dist/vue.esm-bundler.js'
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
