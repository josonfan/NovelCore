<template>
  <div :class="['layout', { collapsed }]">
    <aside class="sidebar">
      <div class="logo-bar" @click="goHome" role="button" tabindex="0" aria-label="返回首页" @keydown.enter="goHome">
        <LogoIcon :size="22" />
      </div>
      <el-skeleton :loading="loading" animated style="padding: 8px">
        <template #template>
          <el-skeleton-item variant="p" style="height: 20px; margin: 8px 0" />
          <el-skeleton-item variant="p" style="height: 20px; margin: 8px 0" />
          <el-skeleton-item variant="p" style="height: 20px; margin: 8px 0" />
        </template>
        <el-menu :default-active="active" class="menu" router :collapse="collapsed" :collapse-transition="false">
          <template v-for="m in menus" :key="m.id">
            <el-sub-menu v-if="m.children && m.children.length" :index="resolveIndex(m)" :title="collapsed ? m.name : ''">
              <template #title>
                <el-icon v-if="resolveIcon(m.icon)" :size="16" class="menu-icon">
                  <component :is="resolveIcon(m.icon)" />
                </el-icon>
                <span class="menu-text">{{ m.name }}</span>
              </template>
              <template v-for="c in m.children" :key="c.id">
                <el-sub-menu v-if="c.children && c.children.length" :index="resolveIndex(c)" :title="collapsed ? c.name : ''">
                  <template #title>
                    <el-icon v-if="resolveIcon(c.icon)" :size="16" class="menu-icon">
                      <component :is="resolveIcon(c.icon)" />
                    </el-icon>
                    <span class="menu-text">{{ c.name }}</span>
                  </template>
                  <el-menu-item v-for="gc in c.children" :key="gc.id" :index="resolveIndex(gc)" :title="collapsed ? gc.name : ''">
                    <el-icon v-if="resolveIcon(gc.icon)" :size="16" class="menu-icon">
                      <component :is="resolveIcon(gc.icon)" />
                    </el-icon>
                    <span class="menu-text">{{ gc.name }}</span>
                  </el-menu-item>
                </el-sub-menu>
                <el-menu-item v-else :index="resolveIndex(c)">
                  <el-icon v-if="resolveIcon(c.icon)" :size="16" class="menu-icon">
                    <component :is="resolveIcon(c.icon)" />
                  </el-icon>
                  {{ c.name }}
                </el-menu-item>
              </template>
            </el-sub-menu>
            <el-menu-item v-else :index="resolveIndex(m)" :title="collapsed ? m.name : ''">
              <el-icon v-if="resolveIcon(m.icon)" :size="16" class="menu-icon">
                <component :is="resolveIcon(m.icon)" />
              </el-icon>
              <span class="menu-text">{{ m.name }}</span>
            </el-menu-item>
          </template>
        </el-menu>
      </el-skeleton>
    </aside>
    <main class="content">
      <header class="header">
        <div style="flex:1;display:flex;align-items:center;gap:8px">
          <el-button text circle @click="toggleCollapse" aria-label="折叠菜单" title="折叠菜单">
            <el-icon :size="18"><component :is="collapseIcon" /></el-icon>
          </el-button>
          <Breadcrumbs />
        </div>
        <div class="header-right">
          <el-popover placement="bottom-end" width="220" trigger="click">
            <template #reference>
              <el-button text circle>
                <el-icon :size="18"><component :is="(Icons as any).Brush || (Icons as any).Setting" /></el-icon>
              </el-button>
            </template>
            <div class="theme-swatches">
              <div
                v-for="c in palettes"
                :key="c.p"
                class="swatch"
                :class="{ 'is-active': c.p === activePrimary }"
                :style="{ background: c.p }"
                @click="applyPalette(c.p, c.s)"
              />
            </div>
          </el-popover>
          <UserDropdown />
        </div>
      </header>
      <section class="body">
        <router-view />
      </section>
    </main>
  </div>
  <el-backtop right="24" bottom="24" />
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store'
import { fetchContext } from '../api/auth'
import * as Icons from '@element-plus/icons-vue'
import LogoIcon from '../components/LogoIcon.vue'
import UserDropdown from '../components/UserDropdown.vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { pathOf } from '../router/routes'

const router = useRouter()
const auth = useAuthStore()
const active = computed(() => router.currentRoute.value.path || '/')
const menus = ref([])
const loading = ref(true)
const collapsed = ref(false)
function goHome(){
  router.push(pathOf('/'))
}
const palettes = ref([ { p: '#3B82F6', s: '#2563EB' }, { p: '#409EFF', s: '#337ecc' }, { p: '#22C55E', s: '#16A34A' }, { p: '#F59E0B', s: '#D97706' }, { p: '#EF4444', s: '#DC2626' }, { p: '#8B5CF6', s: '#7C3AED' } ])
const activePrimary = ref('')

function logout() {}

function resolveIndex(m: any) {
  const p = m.path || m.route || ''
  if (p) return pathOf(p)
  const n = String(m.name || '')
  if (n === '分类管理') return '/content/categories'
  if (n === '标签管理') return '/content/tags'
  if (n === '小说管理') return '/content/novels'
  if (n === '章节管理') return '/content/chapters'
  return pathOf('/')
}

function resolveIcon(name?: string) {
  if (!name) return null
  const n = name.replace(/^el-icon-/, '')
  const pascal = n
    .split(/[-_\s]/)
    .map((s) => s.charAt(0).toUpperCase() + s.slice(1))
    .join('')
  return (Icons as any)[pascal] || null
}

const collapseIcon = computed(() => (collapsed.value ? (Icons as any).Expand : (Icons as any).Fold))
function toggleCollapse() {
  collapsed.value = !collapsed.value
  try {
    localStorage.setItem('nc-sidebar-collapsed', collapsed.value ? '1' : '0')
  } catch (_) {}
}

function applyPalette(p: string, s: string) {
  activePrimary.value = p
  document.documentElement.style.setProperty('--nc-primary', p)
  document.documentElement.style.setProperty('--nc-primary-600', s)
  try {
    localStorage.setItem('nc-primary', p)
    localStorage.setItem('nc-primary600', s)
  } catch (_) {}
}

onMounted(async () => {
  try {
    const token = localStorage.getItem('token') || ''
    if (!token) return
    try {
      collapsed.value = localStorage.getItem('nc-sidebar-collapsed') === '1'
    } catch (_) {}
    try {
      const sp = localStorage.getItem('nc-primary') || ''
      const ss = localStorage.getItem('nc-primary600') || ''
      if (sp && ss) applyPalette(sp, ss)
    } catch (_) {}
    const ctx = await fetchContext()
    auth.setContext(ctx)
    function norm(list: any[]): any[] {
      const arr = Array.isArray(list) ? list : []
      return arr
        .map((m: any) => ({
          ...m,
          children: norm(m?.children || []),
        }))
        .filter((m: any) => String(m?.type || 'menu').toLowerCase() === 'menu' && Number(m?.visible ?? 1) !== 0 && Number(m?.is_active ?? 1) !== 0)
    }
    menus.value = norm(ctx?.menus || [])
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.layout {
  display: grid;
  grid-template-columns: 200px 1fr;
  height: 100vh;
}
.layout .el-menu{ border-right: none; }
.layout.collapsed { grid-template-columns: 64px 1fr; }
.sidebar {
  background: #ffffff;
  border-right: 1px solid #e5e7eb;
  padding: 0;
}
 .logo-bar{ padding: calc((var(--nc-header-height) - 22px)/2) 0; display:flex; align-items:center; justify-content:center; color: var(--nc-primary); border-bottom: 1px solid var(--nc-border); box-sizing: border-box; }
.menu {
  background: transparent;
  border-right: none;
}
.menu-icon {
  margin-right: 8px;
}
.menu-entry{ display:inline-flex; align-items:center; gap: 8px; }
.sidebar svg {
  width: 1em;
  height: 1em;
}
.content {
  display: grid;
  grid-template-rows: var(--nc-header-height) 1fr;
}
.header {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 0 18px;
  border-bottom: 1px solid var(--nc-border);
  box-sizing: border-box;
}
.header-right { display: flex; align-items: center; gap: 12px; }
.body {
  padding: 24px;
}
</style>
