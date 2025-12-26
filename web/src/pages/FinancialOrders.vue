<template>
  <div class="wrap">
    <OrderToolbar
      :filters="filters"
      :loading="loading"
      :total="total"
      @update:filters="onFiltersUpdate"
      @refresh="reload"
    />

    <OrderTable
      :loading="loading"
      :rows="rows"
      :total="total"
      :current-page="page"
      :page-size="limit"
      @detail="openDetail"
      @page-change="onPageChange"
      @size-change="onSizeChange"
    />

    <OrderDetailDialog
      v-model:visible="showDetail"
      :detail="detail"
    />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useOrderList, type OrderFilters } from '../composables/useOrderList'
import { OrderToolbar, OrderTable, OrderDetailDialog } from '../components/orders'

const {
  loading,
  rows,
  total,
  page,
  limit,
  filters,
  showDetail,
  detail,
  load,
  onPageChange,
  onSizeChange,
  reload,
  openDetail,
} = useOrderList()

function onFiltersUpdate(newFilters: OrderFilters) {
  filters.value = newFilters
  reload()
}

onMounted(load)
</script>

<style scoped>
.wrap {
  display: grid;
  gap: 12px;
}
</style>
