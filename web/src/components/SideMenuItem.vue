<template>
  <!-- 有子菜单 -->
  <el-sub-menu
    v-if="hasChildren"
    :index="resolveIndex(menu)"
    :title="collapsed ? menu.name : ''"
  >
    <template #title>
      <el-icon
        v-if="menuIcon"
        :size="16"
        class="menu-icon"
      >
        <component :is="menuIcon" />
      </el-icon>
      <span class="menu-text">{{ menu.name }}</span>
    </template>
    <SideMenuItem
      v-for="child in menu.children"
      :key="child.id"
      :menu="child"
      :collapsed="collapsed"
      :resolve-index="resolveIndex"
    />
  </el-sub-menu>

  <!-- 无子菜单 -->
  <el-menu-item
    v-else
    :index="resolveIndex(menu)"
    :title="collapsed ? menu.name : ''"
  >
    <el-icon
      v-if="menuIcon"
      :size="16"
      class="menu-icon"
    >
      <component :is="menuIcon" />
    </el-icon>
    <span class="menu-text">{{ menu.name }}</span>
  </el-menu-item>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { MenuNode } from '../api/menus'
import { resolveIcon } from '../composables/useSidebar'

const props = defineProps<{
  menu: MenuNode
  collapsed: boolean
  resolveIndex: (m: MenuNode) => string
}>()

const hasChildren = computed(() => props.menu.children && props.menu.children.length > 0)
const menuIcon = computed(() => resolveIcon(props.menu.icon))
</script>

<style scoped>
.menu-icon {
  margin-right: 8px;
}
</style>
