import { createApp } from 'vue'
import { createPinia } from 'pinia'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import './styles/theme.css'
import './styles/typography.css'
import { i18n } from './i18n'
import App from './App.vue'
import router from './router'
import { fetchCategoryList } from './api/categories'
import { fetchTagList } from './api/tags'
import { fetchNovelList } from './api/novels'
import { fetchChapterList } from './api/chapters'
import { http } from './api/http'
import { useAuthStore } from './store'

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.use(ElementPlus)
app.use(i18n)
app.mount('#app')

if (import.meta.env.DEV) {
  ;(async () => {
    const auth = useAuthStore()
    const hasToken = !!(localStorage.getItem('token') || '')
    const u = (import.meta as any).env.VITE_DEV_USERNAME || ''
    const p = (import.meta as any).env.VITE_DEV_PASSWORD || ''
    if (!hasToken && u && p) {
      try {
        const { data } = await http.post('Login/login', { username: u, password: p })
        if (data?.code === 200 && data?.token) auth.setToken(data.token)
      } catch (_) {}
    }
    if (localStorage.getItem('token')) {
      try {
        const [cats, tags, novels, chapters] = await Promise.all([
          fetchCategoryList(),
          fetchTagList(),
          fetchNovelList(),
          fetchChapterList(),
        ])
        console.log('ContentAPI', { categories: cats, tags, novels, chapters })
      } catch (e) {
        console.error(e)
      }
    }
  })()
}
