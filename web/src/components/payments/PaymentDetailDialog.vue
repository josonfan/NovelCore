<template>
  <el-dialog
    :model-value="visible"
    title="渠道详情"
    width="640px"
    @update:model-value="$emit('update:visible', $event)"
  >
    <el-descriptions
      :column="2"
      border
    >
      <el-descriptions-item label="ID">
        {{ detail?.id }}
      </el-descriptions-item>
      <el-descriptions-item label="渠道名称">
        {{ detail?.name }}
      </el-descriptions-item>
      <el-descriptions-item label="USDT">
        <el-tag
          size="small"
          :type="detail?.is_usdt === 1 ? 'success' : 'info'"
        >
          {{ detail?.is_usdt === 1 ? '是' : '否' }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="状态">
        <el-tag
          size="small"
          :type="detail?.status === 1 ? 'success' : 'danger'"
        >
          {{ detail?.status === 1 ? '正常' : '停用' }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="下单数">
        {{ detail?.order_quantity }}
      </el-descriptions-item>
      <el-descriptions-item label="支付数">
        {{ detail?.payment_quantity }}
      </el-descriptions-item>
      <el-descriptions-item label="下单额">
        {{ detail?.place_order }}
      </el-descriptions-item>
      <el-descriptions-item label="支付额">
        {{ detail?.payment }}
      </el-descriptions-item>
      <el-descriptions-item label="限额">
        {{ detail?.limit_price }}
      </el-descriptions-item>
      <el-descriptions-item label="周期额">
        {{ detail?.cycle_price }}
      </el-descriptions-item>
      <el-descriptions-item label="默认">
        <el-tag
          size="small"
          :type="detail?.is_default === 1 ? 'success' : 'info'"
        >
          {{ detail?.is_default === 1 ? '是' : '否' }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="Web">
        <el-tag
          size="small"
          :type="detail?.is_web === 1 ? 'success' : 'info'"
        >
          {{ detail?.is_web === 1 ? '是' : '否' }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="PC禁用">
        <el-tag
          size="small"
          :type="detail?.not_pc === 1 ? 'warning' : 'info'"
        >
          {{ detail?.not_pc === 1 ? '是' : '否' }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="图标">
        {{ detail?.icon_iden }}
      </el-descriptions-item>
      <el-descriptions-item label="支付地址">
        {{ detail?.pay_url }}
      </el-descriptions-item>
      <el-descriptions-item label="补单地址">
        {{ detail?.sup_order_url }}
      </el-descriptions-item>
      <el-descriptions-item label="备注">
        {{ detail?.remarks }}
      </el-descriptions-item>
      <el-descriptions-item label="创建时间">
        {{ detail?.created_at }}
      </el-descriptions-item>
      <el-descriptions-item label="更新时间">
        {{ detail?.updated_at }}
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
import type { PaymentChannel } from '../../composables/usePaymentChannelList'

defineProps<{
  visible: boolean
  detail: PaymentChannel | null
}>()

defineEmits<{
  'update:visible': [value: boolean]
}>()
</script>
