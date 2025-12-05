<template>
  <div class="layout">
    <aside class="sidebar">
      <div class="brand">NovelCore</div>
      <el-skeleton :loading="loading" animated style="padding: 8px">
        <template #template>
          <el-skeleton-item variant="p" style="height: 20px; margin: 8px 0" />
          <el-skeleton-item variant="p" style="height: 20px; margin: 8px 0" />
          <el-skeleton-item variant="p" style="height: 20px; margin: 8px 0" />
        </template>
        <el-menu :default-active="active" class="menu" router>
          <template v-for="m in menus" :key="m.id">
            <el-sub-menu v-if="m.children && m.children.length" :index="resolveIndex(m)">
              <template #title>
                <span>
                  <component :is="resolveIcon(m.icon)" v-if="resolveIcon(m.icon)" style="margin-right:8px" />
                  {{ m.name }}
                </span>
              </template>
              <template v-for="c in m.children" :key="c.id">
                <el-sub-menu v-if="c.children && c.children.length" :index="resolveIndex(c)">
                  <template #title>
                    <span>
                      <component :is="resolveIcon(c.icon)" v-if="resolveIcon(c.icon)" style="margin-right:8px" />
                      {{ c.name }}
                    </span>
                  </template>
                  <el-menu-item v-for="gc in c.children" :key="gc.id" :index="resolveIndex(gc)">
                    <component :is="resolveIcon(gc.icon)" v-if="resolveIcon(gc.icon)" style="margin-right:8px" />
                    {{ gc.name }}
                  </el-menu-item>
                </el-sub-menu>
                <el-menu-item v-else :index="resolveIndex(c)">
                  <component :is="resolveIcon(c.icon)" v-if="resolveIcon(c.icon)" style="margin-right:8px" />
                  {{ c.name }}
                </el-menu-item>
              </template>
            </el-sub-menu>
            <el-menu-item v-else :index="resolveIndex(m)">
              <component :is="resolveIcon(m.icon)" v-if="resolveIcon(m.icon)" style="margin-right:8px" />
              {{ m.name }}
            </el-menu-item>
          </template>
        </el-menu>
      </el-skeleton>
    </aside>
    <main class="content">
      <header class="header">
        <el-button type="primary" @click="logout">退出</el-button>
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
import { fetchMenuTree } from '../api/menus'
import * as Icons from '@element-plus/icons-vue'

const router = useRouter()
const auth = useAuthStore()
const active = computed(() => router.currentRoute.value.path || '/')
const menus = ref([])
const loading = ref(true)

function logout() {
  auth.clear()
  router.push('/login')
}

function resolveIndex(m: any) {
  return m.route || m.path || '/'
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

onMounted(async () => {
  try {
    const token = localStorage.getItem('token') || ''
    if (!token) return
    const data = await fetchMenuTree()
    menus.value = data || []
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.layout {
  display: grid;
  grid-template-columns: 240px 1fr;
  height: 100vh;
}
.sidebar {
  background: #ffffff;
  border-right: 1px solid #e5e7eb;
  padding: 16px;
}
.brand {
  font-weight: 700;
  font-size: 18px;
  margin-bottom: 12px;
}
.menu {
  background: transparent;
  border-right: none;
}
.content {
  display: grid;
  grid-template-rows: 60px 1fr;
}
.header {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 0 16px;
  border-bottom: 1px solid #e5e7eb;
}
.body {
  padding: 16px;
}
</style>
