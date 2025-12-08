import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const base = env.VITE_API_BASE || '/admin'
  const target = env.VITE_API_PROXY_TARGET || 'http://localhost:8000'

  const proxy: Record<string, any> = {}
  if (base && base !== '/') {
    proxy[base] = { target, changeOrigin: true }
  }
  proxy['/api'] = { target, changeOrigin: true }

  return {
    plugins: [vue()],
    server: {
      host: true,
      port: 5173,
      proxy,
    },
  }
})
