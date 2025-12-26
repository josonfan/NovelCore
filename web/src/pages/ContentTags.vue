<template>
  <div class="wrap">
    <TagToolbar
      v-model="keyword"
      :loading="loading"
      :total="total"
      :action-buttons="actionButtons"
      @action="onAction"
      @refresh="load"
      @add="openAdd"
    />

    <TagTable
      :loading="loading"
      :rows="filtered"
      :total="total"
      :current-page="page"
      :page-size="limit"
      :action-buttons="actionButtons"
      @action="onActionRow"
      @edit="openEdit"
      @delete="onDelete"
      @page-change="onPageChange"
      @size-change="onSizeChange"
    />

    <TagFormDialog
      v-model:visible="showForm"
      v-model:active-tab="activeTab"
      :mode="formMode"
      :form="form"
      @update:form="form = $event"
      @save="saveForm"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store'
import { useTagList } from '../composables/useTagList'
import { useTagForm } from '../composables/useTagForm'
import { TagToolbar, TagTable, TagFormDialog } from '../components/tags'
import type { ActionButton } from '../composables/useActionButtons'

const router = useRouter()
const auth = useAuthStore()

// 列表逻辑
const {
  loading,
  total,
  page,
  limit,
  keyword,
  filtered,
  load,
  onPageChange,
  onSizeChange,
  onDelete,
} = useTagList()

// 表单逻辑
const { showForm, formMode, activeTab, form, openAdd, openEdit, saveForm } = useTagForm(load)

// 动态按钮
const actionButtons = computed<ActionButton[]>(() => {
  const path = router.currentRoute.value.path
  function matchNode(list: ActionButton[]): ActionButton | null {
    for (const m of list || []) {
      const p = (m as { path?: string; route?: string })?.path || (m as { route?: string })?.route || ''
      if (p === path || m?.name === '标签管理') return m
      const found = matchNode((m as { children?: ActionButton[] })?.children || [])
      if (found) return found
    }
    return null
  }
  const root = matchNode((auth.context || {}).menus || [])
  const arr = ((root as { children?: ActionButton[] })?.children || []).filter(
    (x: ActionButton) =>
      String((x as { type?: string })?.type || '') === 'button' &&
      Number((x as { visible?: number })?.visible ?? 1) !== 0 &&
      Number((x as { is_active?: number })?.is_active ?? 1) !== 0
  )
  return arr.sort(
    (a: ActionButton, b: ActionButton) =>
      Number((a as { sort_order?: number })?.sort_order ?? 0) -
      Number((b as { sort_order?: number })?.sort_order ?? 0)
  )
})

function onAction(btn: ActionButton) {
  const code = String(btn?.code || '').toLowerCase()
  const name = String(btn?.name || '').toLowerCase()
  const map: Record<string, () => void> = {
    add: openAdd,
    create: openAdd,
    new: openAdd,
    refresh: load,
  }
  const fn = map[code] || map[name]
  if (fn) fn()
}

function onActionRow(btn: ActionButton, row: { id: number | string }) {
  const code = String(btn?.code || '').toLowerCase()
  const name = String(btn?.name || '').toLowerCase()
  const rmap: Record<string, () => void> = {
    edit: () => openEdit(row as Parameters<typeof openEdit>[0]),
    update: () => openEdit(row as Parameters<typeof openEdit>[0]),
    delete: () => onDelete(row as Parameters<typeof onDelete>[0]),
    remove: () => onDelete(row as Parameters<typeof onDelete>[0]),
    view: () => openEdit(row as Parameters<typeof openEdit>[0]),
    detail: () => openEdit(row as Parameters<typeof openEdit>[0]),
  }
  const fn = rmap[code] || rmap[name]
  if (fn) fn()
}

onMounted(load)
</script>

<style scoped>
.wrap {
  display: grid;
  gap: 12px;
}
</style>
