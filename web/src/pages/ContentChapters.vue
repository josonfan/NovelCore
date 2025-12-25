<template>
  <div class="wrap">
    <ChapterToolbar
      v-model="kw"
      :loading="loading"
      :total="total"
      :novel-title="novelTitle"
      :action-buttons="actionButtons"
      @action="handleToolbarAction"
      @refresh="load"
      @add="openAdd"
    />

    <ChapterTable
      :data="filtered"
      :loading="loading"
      :total="total"
      :current-page="page"
      :page-size="limit"
      :action-buttons-row="actionButtonsRow"
      @action-row="handleRowAction"
      @edit="openEdit"
      @view-content="openContent"
      @delete="handleDelete"
      @page-change="onPage"
      @size-change="onSize"
    />

    <ChapterFormDialog
      v-model:visible="showForm"
      v-model:active-tab="activeTab"
      v-model:form="form"
      :mode="formMode"
      :hide-novel-id="!!novelId"
      @save="saveForm"
    />

    <ChapterContentDialog
      v-model:visible="showContent"
      v-model:form="contentForm"
      @save="saveContent"
    />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import {
  ChapterToolbar,
  ChapterTable,
  ChapterFormDialog,
  ChapterContentDialog,
} from '../components/chapters'
import {
  useChapterList,
  useChapterForm,
  useChapterContent,
  useActionButtons,
} from '../composables'
import type { Chapter } from '../api/chapters'
import type { ActionButton } from '../composables/useActionButtons'

// 列表管理
const {
  loading,
  total,
  page,
  limit,
  kw,
  novelId,
  novelTitle,
  filtered,
  load,
  loadNovelTitle,
  checkNovelId,
  onPage,
  onSize,
  handleDelete,
} = useChapterList()

// 表单管理
const {
  showForm,
  formMode,
  activeTab,
  form,
  openAdd,
  openEdit,
  saveForm,
} = useChapterForm(load, () => novelId.value)

// 内容管理
const {
  showContent,
  contentForm,
  openContent,
  saveContent,
} = useChapterContent(load)

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
function handleRowAction(btn: ActionButton, row: Chapter) {
  const code = String(btn?.code || '').toLowerCase()
  const name = String(btn?.name || '').toLowerCase()
  const map: Record<string, () => void> = {
    edit: () => openEdit(row),
    update: () => openEdit(row),
    delete: () => handleDelete(row),
    remove: () => handleDelete(row),
    content: () => openContent(row),
    view: () => openContent(row),
    detail: () => openContent(row),
  }
  const fn = map[code] || map[name]
  if (fn) fn()
}

onMounted(() => {
  if (!checkNovelId()) return
  load()
  loadNovelTitle()
})
</script>

<style scoped>
.wrap {
  display: grid;
  gap: 12px;
}
</style>
