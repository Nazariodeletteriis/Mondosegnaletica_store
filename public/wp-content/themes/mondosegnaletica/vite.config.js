import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  // Base path per gli asset nel tema WordPress
  base: '/wp-content/themes/mondosegnaletica/assets/dist/',

  build: {
    outDir: 'assets/dist',
    emptyOutDir: true,
    manifest: true,

    rollupOptions: {
      input: {
        // Entry points
        main: resolve(__dirname, 'assets/src/js/main.js'),
        'main-css': resolve(__dirname, 'assets/src/css/main.css'),
      },
      output: {
        entryFileNames: '[name]-[hash].js',
        chunkFileNames: '[name]-[hash].js',
        assetFileNames: '[name]-[hash][extname]',
      },
    },

    // Target browser moderni (no legacy transpile — il sito è B2B, utenti professionali)
    target: ['chrome90', 'firefox88', 'safari14', 'edge90'],
    minify: 'esbuild',
    sourcemap: false, // true in dev se necessario
  },

  css: {
    devSourcemap: true,
  },

  // Dev server — proxy a DDEV
  server: {
    port: 5173,
    origin: 'http://localhost:5173',
    // Hot reload degli asset PHP-rendered tramite proxy
    proxy: {
      // Non proxiamo tutto — solo asset. WP gira su DDEV separato.
    },
  },
});
