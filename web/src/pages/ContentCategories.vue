<template>
  <div class="wrap">
    <CategoryToolbar
      v-model="kw"
      :loading="loading"
      :total="total"
      :action-buttons="actionButtons"
      @action="handleToolbarAction"
      @refresh="load"
      @add="openAdd"
    />

    <CategoryTable
      :data="filtered"
      :loading="loading"
      :total="total"
      :current-page="page"
      :page-size="limit"
      :action-buttons-row="actionButtonsRow"
      @action-row="handleRowAction"
      @edit="openEdit"
      @delete="handleDelete"
      @page-change="onPage"
      @size-change="onSize"
    />

    <CategoryFormDialog
      v-model:visible="showForm"
      v-model:active-tab="activeTab"
      v-model:form="form"
      :mode="formMode"
      @save="saveForm"
    />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import {
  CategoryToolbar,
  CategoryTable,
  CategoryFormDialog,
} from '../components/categories'
import {
  useCategoryList,
  useCategoryForm,
  useActionButtons,
} from '../composables'
import type { Category } from '../api/categories'
import type { ActionButton } from '../composables/useActionButtons'

// 列表管理
const {
  loading,
  total,
  page,
  limit,
  kw,
  filtered,
  load,
  onPage,
  onSize,
  handleDelete,
} = useCategoryList()

// 表单管理
const {
  showForm,
  formMode,
  activeTab,
  form,
  openAdd,
  openEdit,
  saveForm,
} = useCategoryForm(load)

// 操作按钮
const { actionButtons, actionButtonsRow } = useActionButtons()

// 工具栏按钮操作
function handleToolbarAction(btn: ActionButton) {
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

// 行按钮操作
function handleRowAction(btn: ActionButton, row: Category) {
  const code = String(btn?.code || '').toLowerCase()
  const name = String(btn?.name || '').toLowerCase()
  const map: Record<string, () => void> = {
    edit: () => openEdit(row),
    update: () => openEdit(row),
    delete: () => handleDelete(row),
    remove: () => handleDelete(row),
    view: () => openEdit(row),
    detail: () => openEdit(row),
  }
  const fn = map[code] || map[name]
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
