<template>
  <div class="wrap">
    <PaymentToolbar
      v-model="keyword"
      :loading="loading"
      :total="total"
      @refresh="load"
      @add="openAdd"
    />

    <PaymentTable
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

    <PaymentFormDialog
      v-model:visible="showForm"
      :mode="formMode"
      :form="form"
      @update:form="form = $event"
      @save="saveForm"
    />

    <PaymentDetailDialog
      v-model:visible="showDetail"
      :detail="detail"
    />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { usePaymentChannelList } from '../composables/usePaymentChannelList'
import { usePaymentChannelForm } from '../composables/usePaymentChannelForm'
import {
  PaymentToolbar,
  PaymentTable,
  PaymentFormDialog,
  PaymentDetailDialog,
} from '../components/payments'

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
} = usePaymentChannelList()

// 表单逻辑
const { showForm, formMode, form, openAdd, openEdit, saveForm } = usePaymentChannelForm(load)

onMounted(load)
</script>

<style scoped>
.wrap {
  display: grid;
  gap: 12px;
}
</style>
