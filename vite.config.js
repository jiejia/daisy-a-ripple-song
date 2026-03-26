import { defineConfig } from 'vite';
import path from 'node:path';
import tailwindcss from '@tailwindcss/vite'


export default defineConfig({
  plugins: [
    tailwindcss(),
  ],
  base: './',
  publicDir: false,
  server: {
    host: '127.0.0.1',
    port: 5173,
    strictPort: true,
    cors: true
  },
  build: {
    outDir: 'public/dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: [
        path.resolve(__dirname, 'resources/js/main.js'),
        path.resolve(__dirname, 'resources/css/main.css')
      ]
    }
  }
});
