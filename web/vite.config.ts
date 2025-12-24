import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd())
  return {
    plugins: [vue()],
    build: {
      sourcemap: mode === 'development',
      chunkSizeWarningLimit: 1500,
      rollupOptions: {
        output: {
          manualChunks(id) {
            if (id.includes('node_modules')) {
              if (id.includes('element-plus')) return 'vendor_element_plus'
              if (id.includes('@element-plus/icons-vue')) return 'vendor_ep_icons'
              if (id.includes('vue-router')) return 'vendor_router'
              if (id.includes('pinia')) return 'vendor_pinia'
              if (id.includes('vue')) return 'vendor_vue'
              if (id.includes('axios')) return 'vendor_axios'
              if (id.includes('quill')) return 'vendor_quill'
            }
          },
        },
      },
    },
    server: {
      hmr: { overlay: false },
      proxy: {
        '/admin': {
          target: env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000',
          changeOrigin: true,
        },
        '/Login': { target: env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000', changeOrigin: true },
        '/Menus': { target: env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000', changeOrigin: true },
        '/Tags': { target: env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000', changeOrigin: true },
        '/Categories': { target: env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000', changeOrigin: true },
        '/Novels': { target: env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000', changeOrigin: true },
        '/Vip': { target: env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000', changeOrigin: true },
        '/Sites': { target: env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000', changeOrigin: true },
        '/DomainList': { target: env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000', changeOrigin: true },
        '/PaymentChannel': { target: env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000', changeOrigin: true },
        '/SiteOrders': { target: env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000', changeOrigin: true },
        '/Upload': { target: env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000', changeOrigin: true },
      },
    },
  }
})
