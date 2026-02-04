import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import os from 'os';

function getLocalIP() {
    const interfaces = os.networkInterfaces();

    if (!interfaces) return 'localhost';

    for (const name of Object.keys(interfaces)) {
        const networkInterface = interfaces[name];

        if (networkInterface) {
            for (const iface of networkInterface) {
                if (iface.family === 'IPv4' && !iface.internal) {
                    return iface.address;
                }
            }
        }
    }
    return 'localhost';
}

const localIP = getLocalIP();
console.log(`📡 Host détecté : ${localIP}`);

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: '0.0.0.0',
        cors: {
            origin: '*',
        },
        hmr: {
            host: localIP,
        },
        watch: {
            usePolling: true,
        },
    },
});