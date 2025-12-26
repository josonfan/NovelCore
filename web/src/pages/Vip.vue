<template>
  <div class="wrap">
    <VipToolbar
      v-model="keyword"
      :loading="loading"
      :total="total"
      @refresh="load"
      @add="openAdd"
    />

    <VipTable
      :loading="loading"
      :rows="filtered"
      :total="total"
      :current-page="page"
      :page-size="limit"
      @detail="openDetail"
      @edit="openEdit"
      @toggle="onToggle"
      @delete="onDelete"
      @page-change="onPageChange"
      @size-change="onSizeChange"
    />

    <VipFormDialog
      v-model:visible="showForm"
      :mode="formMode"
      :form="form"
      @update:form="form = $event"
      @save="saveForm"
    />

    <VipDetailDialog
      v-model:visible="showDetail"
      :detail="detail"
    />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useVipList } from '../composables/useVipList'
import { useVipForm } from '../composables/useVipForm'
import {
  VipToolbar,
  VipTable,
  VipFormDialog,
  VipDetailDialog,
} from '../components/vip'

// 列表逻辑
const {
  loading,
  total,
  page,
  limit,
  keyword,
  filtered,
  showDetail,
  detail,
  load,
  onPageChange,
  onSizeChange,
  onToggle,
  onDelete,
  openDetail,
} = useVipList()

// 表单逻辑
const { showForm, formMode, form, openAdd, openEdit, saveForm } = useVipForm(load)

onMounted(load)
</script>

<style scoped>
.wrap {
  display: grid;
  gap: 12px;
}
</style>
