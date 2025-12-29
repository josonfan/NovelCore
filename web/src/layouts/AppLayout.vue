<template>
  <div :class="['layout', { 'is-collapsed': collapsed }]">
    <!-- 侧边栏 -->
    <aside class="sidebar">
      <div
        class="logo-bar"
        role="button"
        tabindex="0"
        aria-label="返回首页"
        @click="goHome"
        @keydown.enter="goHome"
      >
        <LogoIcon :size="22" />
      </div>

      <el-skeleton
        :loading="loading"
        animated
        class="menu-skeleton"
      >
        <template #template>
          <el-skeleton-item
            v-for="i in 3"
            :key="i"
            variant="p"
            class="skeleton-item"
          />
        </template>

        <el-scrollbar class="menu-scrollbar">
          <el-menu
            :default-active="activeRoute"
            class="sidebar-menu"
            router
            :collapse="collapsed"
            :collapse-transition="false"
          >
            <SideMenuItem
              v-for="menu in menus"
              :key="menu.id"
              :menu="menu"
              :collapsed="collapsed"
              :resolve-index="resolveIndex"
            />
          </el-menu>
        </el-scrollbar>
      </el-skeleton>
    </aside>

    <!-- 主内容区 -->
    <main class="main">
      <header class="header">
        <div class="header-left">
          <el-button
            text
            circle
            aria-label="折叠菜单"
            title="折叠菜单"
            @click="toggleSidebar"
          >
            <el-icon :size="18">
              <component :is="collapseIcon" />
            </el-icon>
          </el-button>
          <Breadcrumbs />
        </div>

        <div class="header-right">
          <!-- 主题选择器 -->
          <el-popover
            placement="bottom-end"
            :width="220"
            trigger="click"
          >
            <template #reference>
              <el-button
                text
                circle
                title="主题色"
              >
                <el-icon :size="18">
                  <Brush />
                </el-icon>
              </el-button>
            </template>
            <div class="theme-panel">
              <div class="theme-label">
                选择主题色
              </div>
              <div class="theme-swatches">
                <div
                  v-for="(palette, idx) in palettes"
                  :key="idx"
                  class="swatch"
                  :class="{ 'is-active': palette.primary === activePrimary }"
                  :style="{ background: palette.primary }"
                  :title="palette.primary"
                  @click="applyPalette(palette)"
                />
              </div>
            </div>
          </el-popover>

          <UserDropdown />
        </div>
      </header>

      <section class="body">
        <router-view v-slot="{ Component }">
          <transition
            name="fade"
            mode="out-in"
          >
            <component :is="Component" />
          </transition>
        </router-view>
      </section>
    </main>
  </div>

  <el-backtop
    :right="24"
    :bottom="24"
  />
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Brush } from '@element-plus/icons-vue'

// Components
import LogoIcon from '../components/LogoIcon.vue'
import UserDropdown from '../components/UserDropdown.vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import SideMenuItem from '../components/SideMenuItem.vue'

// Composables
import { useSidebar } from '../composables/useSidebar'
import { useTheme } from '../composables/useTheme'
import { useMenus } from '../composables/useMenus'
import { pathOf } from '../router/routes'

const router = useRouter()

// 侧边栏状态
const { collapsed, collapseIcon, toggle: toggleSidebar } = useSidebar()

// 主题管理
const { palettes, activePrimary, applyPalette } = useTheme()

// 菜单数据
const { menus, loading, resolveIndex, loadMenus } = useMenus()

// 当前激活路由
const activeRoute = computed(() => router.currentRoute.value.path || '/')

// 返回首页
function goHome() {
  router.push(pathOf('/'))
}

onMounted(loadMenus)
</script>

<style scoped>
/* ========== 布局结构 ========== */
.layout {
  display: grid;
  grid-template-columns: var(--sidebar-width, 200px) 1fr;
  height: 100vh;
  transition: grid-template-columns 0.2s ease;
}

.layout.is-collapsed {
  --sidebar-width: 64px;
}

/* ========== 侧边栏 ========== */
.sidebar {
  display: flex;
  flex-direction: column;
  background: var(--nc-bg, #fff);
  border-right: 1px solid var(--nc-border, #e5e7eb);
  overflow: hidden;
}

.logo-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: var(--nc-header-height, 56px);
  color: var(--nc-primary);
  border-bottom: 1px solid var(--nc-border, #e5e7eb);
  cursor: pointer;
  flex-shrink: 0;
  transition: color 0.2s;
  box-sizing: border-box;
}

.logo-bar:hover {
  opacity: 0.85;
}

.menu-skeleton {
  padding: 8px;
  flex: 1;
}

.skeleton-item {
  height: 20px;
  margin: 8px 0;
}

.menu-scrollbar {
  flex: 1;
}

.sidebar-menu {
  border-right: none;
  background: transparent;
}

.sidebar-menu :deep(.el-menu-item),
.sidebar-menu :deep(.el-sub-menu__title) {
  height: 44px;
  line-height: 44px;
}

/* ========== 主内容区 ========== */
.main {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: var(--nc-bg-page, #f5f7fa);
}

.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: var(--nc-header-height, 56px);
  padding: 0 18px;
  background: var(--nc-bg, #fff);
  border-bottom: 1px solid var(--nc-border, #e5e7eb);
  flex-shrink: 0;
  box-sizing: border-box;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.body {
  flex: 1;
  padding: 24px;
  overflow: auto;
}

/* ========== 主题选择器 ========== */
.theme-panel {
  padding: 4px 0;
}

.theme-label {
  font-size: 12px;
  color: var(--nc-muted, #6b7280);
  margin-bottom: 12px;
}

.theme-swatches {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.swatch {
  width: 28px;
  aspect-ratio: 1;
  border-radius: 6px;
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.15s;
  outline: 2px solid transparent;
  outline-offset: -2px;
}

.swatch:hover {
  transform: scale(1.1);
}

.swatch.is-active {
  outline-color: var(--nc-text, #1f2937);
  box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
}

/* ========== 路由过渡动画 ========== */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
