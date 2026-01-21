<template>
  <div class="wrap">
    <NovelToolbar
      v-model="kw"
      v-model:filters="filters"
      :loading="loading"
      :total="total"
      :action-buttons="actionButtons"
      :categories="categories"
      @action="handleToolbarAction"
      @refresh="load"
      @add="openAdd"
      @filter-change="onFilterChange"
    />

    <NovelTable
      :data="filtered"
      :loading="loading"
      :total="total"
      :current-page="page"
      :page-size="limit"
      :action-buttons-row="actionButtonsRow"
      @selection-change="onSelect"
      @action-row="handleRowAction"
      @audit="handleAuditRow"
      @chapters="goChapters"
      @tags="openTags"
      @edit="openEdit"
      @delete="handleDelete"
      @page-change="onPage"
      @size-change="onSize"
    />

    <NovelFormDialog
      v-model:visible="showForm"
      v-model:active-tab="activeTab"
      v-model:form="form"
      :mode="formMode"
      :categories="categories"
      :submitting="submitting"
      @save="saveForm"
    />

    <NovelAuditDialog
      v-model:visible="showAudit"
      :audit="audit"
      @submit="submitAudit"
    />

    <NovelTagsDialog
      v-model:visible="showTags"
      :loading="tagsLoading"
      :tag-groups="tagGroups"
      :selected-tags="selectedTags"
      @update:selected-tags="updateSelectedTags"
      @submit="submitTags"
    />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  NovelToolbar,
  NovelTable,
  NovelFormDialog,
  NovelAuditDialog,
  NovelTagsDialog,
} from '../components/novels'
import {
  useNovelList,
  useNovelForm,
  useNovelAudit,
  useNovelTags,
  useActionButtons,
} from '../composables'
import type { Novel } from '../api/novels'
import type { ActionButton } from '../composables/useActionButtons'

const router = useRouter()

// 列表管理
const {
  loading,
  total,
  page,
  limit,
  kw,
  categories,
  selected,
  filtered,
  filters,
  load,
  onPage,
  onSize,
  onSelect,
  onFilterChange,
  handleDelete,
} = useNovelList()

// 表单管理
const {
  showForm,
  formMode,
  form,
  activeTab,
  submitting,
  openAdd,
  openEdit,
  saveForm,
} = useNovelForm(load)

// 审核管理
const {
  showAudit,
  audit,
  openAudit: openAuditDialog,
  submitAudit,
} = useNovelAudit(load)

// 标签管理
const {
  showTags,
  tagGroups,
  tagsLoading,
  selectedTags,
  openTags,
  submitTags,
} = useNovelTags()

// 操作按钮
const { actionButtons, actionButtonsRow } = useActionButtons()

// 打开审核（支持多选）
function openAudit(ids?: Array<number | string>) {
  openAuditDialog(ids, selected.value)
}

// 单行审核
function handleAuditRow(row: Novel) {
  openAudit([row.id])
}

// 更新标签选择
function updateSelectedTags(type: string, ids: Array<number | string>) {
  selectedTags.value[type] = ids
}

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
function handleRowAction(btn: ActionButton, row: Novel) {
  const code = String(btn?.code || '').toLowerCase()
  const name = String(btn?.name || '').toLowerCase()
  const map: Record<string, () => void> = {
    edit: () => openEdit(row),
    update: () => openEdit(row),
    delete: () => handleDelete(row),
    remove: () => handleDelete(row),
    view: () => openEdit(row),
    detail: () => openEdit(row),
    review: () => openAudit([row.id]),
    audit: () => openAudit([row.id]),
    approve: () => {
      audit.value = { ids: [row.id], status: 1, reason: '' }
      showAudit.value = true
    },
    reject: () => {
      audit.value = { ids: [row.id], status: 2, reason: '' }
      showAudit.value = true
    },
  }
  const fn = map[code] || map[name]
  if (fn) fn()
}

// 跳转章节列表
function goChapters(row: Novel) {
  if (!row?.id) return
  router.push({ name: 'content-novel-chapters', params: { id: row.id } })
}

onMounted(load)
</script>

<style scoped>
.wrap {
  display: grid;
  gap: 12px;
}
</style>
