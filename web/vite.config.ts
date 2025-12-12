import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd())
  return {
    plugins: [vue()],
    build: {
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
      },
    },
  }
})
