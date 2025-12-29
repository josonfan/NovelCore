<template>
  <div class="page-container">
    <!-- 工具栏 -->
    <MenuToolbar
      v-model:parent-id="filters.parentId"
      v-model:keyword="filters.keyword"
      :loading="loading"
      :total="total"
      :current-parent-label="currentParentLabel"
      :options="options"
      @refresh="refresh"
      @add="openAdd(filters.parentId)"
    />

    <!-- 树形表格 -->
    <MenuTable
      :data="filteredRows"
      :loading="loading"
      :current-page="page"
      :page-size="limit"
      :total="total"
      @edit="openEdit"
      @delete="handleDelete"
      @detail="handleDetail"
      @bind="openBind"
      @add-child="openAddChild"
      @page-change="onPageChange"
      @size-change="onSizeChange"
    />

    <!-- 新建/编辑弹窗 -->
    <MenuFormDialog
      v-model="showForm"
      :mode="formMode"
      :form="form"
      :preview-icon="previewIcon"
      :options="options"
      @save="saveForm"
    />

    <!-- 详情弹窗 -->
    <MenuDetailDialog
      v-model="showDetail"
      :detail="detail"
    />

    <!-- 权限绑定弹窗 -->
    <MenuBindDialog
      v-model="showBind"
      :perm-options="permOptions"
      :perm-ids="bindPermIds"
      @update:perm-ids="bindPermIds = $event"
      @save="saveBind"
    />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import {
  MenuToolbar,
  MenuTable,
  MenuFormDialog,
  MenuDetailDialog,
  MenuBindDialog,
} from '../components/menus'
import { useMenuManage } from '../composables/useMenuManage'
import { useMenuForm } from '../composables/useMenuForm'

// 列表管理
const {
  loading,
  page,
  limit,
  total,
  options,
  filters,
  filteredRows,
  currentParentLabel,
  showDetail,
  detail,
  load,
  loadOptions,
  refresh,
  onPageChange,
  onSizeChange,
  handleDelete,
  handleDetail,
} = useMenuManage()

// 表单管理
const {
  showForm,
  formMode,
  form,
  previewIcon,
  openAdd,
  openEdit,
  openAddChild,
  saveForm,
  showBind,
  permOptions,
  bindPermIds,
  openBind,
  saveBind,
} = useMenuForm(load, loadOptions)

onMounted(() => {
  load()
  loadOptions()
})
</script>

<style scoped>
.page-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
</style>
