import { defineConfig } from 'vite';
import { resolve } from 'path';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [tailwindcss()],

  base: '/wp-content/themes/mondosegnaletica/assets/dist/',

  build: {
    outDir: 'assets/dist',
    emptyOutDir: true,
    manifest: true,

    rollupOptions: {
      input: {
        main: resolve(__dirname, 'assets/src/js/main.js'),
        hero: resolve(__dirname, 'assets/src/js/hero.js'),
        'main-css': resolve(__dirname, 'assets/src/css/main.css'),
      },
      output: {
        entryFileNames: '[name]-[hash].js',
        chunkFileNames: '[name]-[hash].js',
        assetFileNames: '[name]-[hash][extname]',
      },
    },

    target: ['chrome90', 'firefox88', 'safari14', 'edge90'],
    minify: 'esbuild',
    sourcemap: false,
  },

  css: {
    devSourcemap: true,
  },

  server: {
    port: 5173,
    origin: 'http://localhost:5173',
  },
});
