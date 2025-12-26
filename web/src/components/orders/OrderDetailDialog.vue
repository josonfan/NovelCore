<template>
  <el-dialog
    :model-value="visible"
    title="订单详情"
    width="860px"
    draggable
    @update:model-value="$emit('update:visible', $event)"
  >
    <el-descriptions
      v-if="detail"
      :column="descCols"
      border
    >
      <el-descriptions-item label="ID">
        {{ detail.id }}
      </el-descriptions-item>
      <el-descriptions-item label="站点ID">
        {{ detail.site_id }}
      </el-descriptions-item>
      <el-descriptions-item label="订单ID">
        {{ detail.order_id }}
      </el-descriptions-item>
      <el-descriptions-item label="订单号">
        {{ detail.order_no }}
      </el-descriptions-item>
      <el-descriptions-item label="用户ID">
        {{ detail.user_id }}
      </el-descriptions-item>
      <el-descriptions-item label="类型">
        {{ detail.order_type }}
      </el-descriptions-item>
      <el-descriptions-item label="商品ID">
        {{ detail.good_id }}
      </el-descriptions-item>
      <el-descriptions-item label="商品名称">
        {{ detail.good_info?.name || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="商品天数">
        {{ detail.good_info?.days ?? '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="商品金额">
        {{ detail.good_info?.amount || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="商品描述">
        {{ detail.good_info?.descript || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="原价">
        {{ detail.good_info?.old_price || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="金额">
        {{ detail.amount }}
      </el-descriptions-item>
      <el-descriptions-item label="渠道ID">
        {{ detail.pay_channel_id ?? '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="状态">
        <el-tag
          size="small"
          :type="getStatusType(detail.status)"
        >
          {{ getStatusText(detail.status) }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="客户端">
        {{ detail.extra?.client || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="来源">
        {{ detail.extra?.created_by || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="扩展天数">
        {{ detail.extra?.days ?? '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="创建时间">
        {{ detail.created_at || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="更新时间">
        {{ detail.updated_at || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="支付时间">
        {{ detail.paid_at ?? '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="最后同步">
        {{ detail.last_synced_at || '-' }}
      </el-descriptions-item>
    </el-descriptions>
    <template #footer>
      <el-button
        type="primary"
        @click="$emit('update:visible', false)"
      >
        关闭
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { getStatusText, getStatusType, type SiteOrderItem } from '../../composables/useOrderList'

defineProps<{
  visible: boolean
  detail: SiteOrderItem | null
}>()

defineEmits<{
  'update:visible': [value: boolean]
}>()

const descCols = ref(2)

function updateCols() {
  descCols.value = window.innerWidth < 900 ? 1 : 2
}

onMounted(() => {
  updateCols()
  window.addEventListener('resize', updateCols)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateCols)
})
</script>

<style scoped>
:deep(.el-dialog) {
  max-width: 96vw;
}

:deep(.el-dialog__body) {
  max-height: 70vh;
  overflow: auto;
}

:deep(.el-descriptions__table) {
  table-layout: fixed;
  width: 100%;
}

:deep(.el-descriptions__content),
:deep(.el-descriptions__label) {
  word-break: break-all;
  white-space: normal;
}
</style>
