import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
    tailwindcss(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  server: {
    proxy: {
      // Proxy all requests starting with `/api` to your Laravel backend
      '/api': {
        target: 'http://127.0.0.1:8000', // Your Laravel backend URL
        changeOrigin: true, // Needed for virtual hosted sites
        secure: false, // If you're using HTTPS, set this to true
        // Remove the rewrite function to preserve the `/api` prefix
      },
      '/storage': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
      },
    },
  },
})
