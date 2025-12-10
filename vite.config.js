import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel([
            'resources/sass/app.scss',
            'resources/js/app.js',
            'resources/js/chart-config.js',
        ])
    ],
    css: {
        preprocessorOptions: {
            scss: {
                quietDeps: true,
                silenceDeprecations: ['import']
            }
        }
    },
    build: {
        minify: 'esbuild',
        target: 'esnext',
        sourcemap: false,
        chunkSizeWarningLimit: 1000,
        cssCodeSplit: false,
        cssMinify: false,
        rollupOptions: {
            output: {
                manualChunks: undefined
            },
            external: []
        }
    },
    optimizeDeps: {
        include: [
            'jquery',
            'bootstrap',
            'popper.js',
            '@coreui/coreui',
            '@coreui/icons',
            'axios',
            'chart.js'
        ]
    }
});
