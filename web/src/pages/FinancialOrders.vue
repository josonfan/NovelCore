<template>
  <div class="wrap">
    <el-card shadow="never" class="toolbar">
      <div class="toolbar-grid">
        <div class="filters">
          <el-input v-model="filters.order_no" placeholder="订单号" clearable />
          <el-input v-model="filters.user_id" placeholder="用户ID" clearable />
          <el-select v-model="filters.status" placeholder="状态" clearable style="width: 180px">
            <el-option label="待处理" value="pending" />
            <el-option label="处理中" value="processing" />
            <el-option label="已支付" value="paid" />
            <el-option label="已取消" value="cancelled" />
            <el-option label="已退款" value="refunded" />
          </el-select>
          <el-input v-model="filters.site_id" placeholder="站点ID" clearable />
        </div>
        <div class="actions">
          <el-button @click="reload" :loading="loading">刷新</el-button>
        </div>
      </div>
      <div class="subline">共 {{ total }} 条</div>
    </el-card>
    <el-card shadow="hover" class="table-card">
      <el-table :data="rows" v-loading="loading" border size="small" stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="site_id" label="站点ID" width="90" />
        <el-table-column prop="order_id" label="订单ID" width="100" />
        <el-table-column prop="order_no" label="订单号" min-width="180" />
        <el-table-column prop="user_id" label="用户ID" width="100" />
        <el-table-column prop="order_type" label="类型" width="100" />
        <el-table-column prop="good_id" label="商品ID" width="100" />
        <el-table-column label="商品信息" min-width="220">
          <template #default="{ row }">
            <div>名称：{{ row.good_info?.name || '-' }}</div>
            <div>天数：{{ row.good_info?.days ?? '-' }} 金额：{{ row.good_info?.amount || '-' }}</div>
            <div>描述：{{ row.good_info?.descript || '-' }}</div>
          </template>
        </el-table-column>
        <el-table-column prop="amount" label="金额" width="120" />
        <el-table-column prop="pay_channel_id" label="渠道ID" width="110" />
        <el-table-column label="状态" width="110">
          <template #default="{ row }">
            <el-tag size="small" :type="statusType(row.status)">{{ statusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="扩展" min-width="240">
          <template #default="{ row }">
            <div>客户端：{{ row.extra?.client || '-' }}</div>
            <div>来源：{{ row.extra?.created_by || '-' }} 天数：{{ row.extra?.days ?? '-' }}</div>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" min-width="160" />
        <el-table-column prop="updated_at" label="更新时间" min-width="160" />
        <el-table-column prop="paid_at" label="支付时间" min-width="160" />
        <el-table-column prop="last_synced_at" label="最后同步" min-width="160" />
        <el-table-column label="操作" width="160" fixed="right">
          <template #default="{ row }">
            <el-button link @click="onDetail(row)">详情</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="pager">
        <el-pagination background layout="prev, pager, next, jumper, sizes, total" :page-size="limit" :current-page="page" :total="total" @current-change="onPage" @size-change="onSize" :page-sizes="[10,20,50]" />
      </div>
    </el-card>
    <el-dialog v-model="showDetail" title="订单详情" width="860px" draggable>
      <el-descriptions :column="descCols" border v-if="detail">
        <el-descriptions-item label="ID">{{ detail?.id }}</el-descriptions-item>
        <el-descriptions-item label="站点ID">{{ detail?.site_id }}</el-descriptions-item>
        <el-descriptions-item label="订单ID">{{ detail?.order_id }}</el-descriptions-item>
        <el-descriptions-item label="订单号">{{ detail?.order_no }}</el-descriptions-item>
        <el-descriptions-item label="用户ID">{{ detail?.user_id }}</el-descriptions-item>
        <el-descriptions-item label="类型">{{ detail?.order_type }}</el-descriptions-item>
        <el-descriptions-item label="商品ID">{{ detail?.good_id }}</el-descriptions-item>
        <el-descriptions-item label="商品名称">{{ detail?.good_info?.name }}</el-descriptions-item>
        <el-descriptions-item label="商品天数">{{ detail?.good_info?.days }}</el-descriptions-item>
        <el-descriptions-item label="商品金额">{{ detail?.good_info?.amount }}</el-descriptions-item>
        <el-descriptions-item label="商品描述">{{ detail?.good_info?.descript }}</el-descriptions-item>
        <el-descriptions-item label="原价">{{ detail?.good_info?.old_price }}</el-descriptions-item>
        <el-descriptions-item label="金额">{{ detail?.amount }}</el-descriptions-item>
        <el-descriptions-item label="渠道ID">{{ detail?.pay_channel_id ?? '-' }}</el-descriptions-item>
        <el-descriptions-item label="状态">{{ statusText(detail?.status as any) }}</el-descriptions-item>
        <el-descriptions-item label="客户端">{{ detail?.extra?.client }}</el-descriptions-item>
        <el-descriptions-item label="来源">{{ detail?.extra?.created_by }}</el-descriptions-item>
        <el-descriptions-item label="扩展天数">{{ detail?.extra?.days }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ detail?.created_at }}</el-descriptions-item>
        <el-descriptions-item label="更新时间">{{ detail?.updated_at }}</el-descriptions-item>
        <el-descriptions-item label="支付时间">{{ detail?.paid_at ?? '-' }}</el-descriptions-item>
        <el-descriptions-item label="最后同步">{{ detail?.last_synced_at }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
  </template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { ElMessage } from 'element-plus'
import { fetchSiteOrdersList, fetchSiteOrderDetail, type OrderStatus, type SiteOrderItem } from '../api/siteOrders'

const page = ref(1)
const limit = ref(10)
const total = ref(0)
const rows = ref<SiteOrderItem[]>([])
const loading = ref(false)
const filters = ref<{ order_no?: string; user_id?: string; status?: OrderStatus; site_id?: string | number }>({})
const showDetail = ref(false)
const detail = ref<SiteOrderItem|null>(null)
const descCols = ref(2)

function statusText(s?: OrderStatus){ return s==='pending'?'待处理':s==='processing'?'处理中':s==='paid'?'已支付':s==='cancelled'?'已取消':s==='refunded'?'已退款':'-' }
function statusType(s?: OrderStatus){ return s==='paid'?'success':s==='processing'?'warning':s==='pending'?'info':s==='cancelled'?'danger':s==='refunded'?'danger':'info' }

async function load() {
  loading.value = true
  try {
    const data = await fetchSiteOrdersList({ page: page.value, limit: limit.value, order_no: filters.value.order_no, user_id: filters.value.user_id, status: filters.value.status, site_id: filters.value.site_id })
    rows.value = data?.list || []
    total.value = data?.count || 0
  } catch (e: any) {
    const resp = e?.response?.data
    ElMessage.error(resp?.message || resp?.msg || '加载失败')
    rows.value = []
    total.value = 0
  } finally {
    loading.value = false
  }
}
function onPage(p: number){ page.value = p; load() }
function onSize(s: number){ limit.value = s; page.value = 1; load() }
function reload(){ page.value = 1; load() }

async function onDetail(row: SiteOrderItem){
  try{
    const d = await fetchSiteOrderDetail(row.id)
    detail.value = d
    showDetail.value = true
  }catch(e:any){
    const resp=e?.response?.data
    ElMessage.error(resp?.message||resp?.msg||'获取详情失败')
  }
}

onMounted(load)
function updateCols(){ descCols.value = window.innerWidth < 900 ? 1 : 2 }
onMounted(() => { updateCols(); window.addEventListener('resize', updateCols) })
onUnmounted(() => { window.removeEventListener('resize', updateCols) })
</script>

<style scoped>
.wrap{ display:grid; gap:12px }
.toolbar{ display:grid; gap:8px }
.toolbar-grid{ display:grid; grid-template-columns: 1fr auto; gap:12px; align-items:center }
.toolbar-grid .filters{ display:inline-grid; grid-template-columns: repeat(4, minmax(160px, 1fr)); gap:8px }
.toolbar-grid .actions{ display:inline-flex; gap:8px; justify-self:end }
.subline{ color: var(--nc-muted); font-size:12px }
.pager{ display:flex; justify-content:flex-end; margin-top:12px }
:deep(.el-dialog){ max-width: 96vw; }
:deep(.el-dialog__body){ max-height: 70vh; overflow: auto; }
:deep(.el-descriptions__table){ table-layout: fixed; width: 100%; }
:deep(.el-descriptions__content), :deep(.el-descriptions__label){ word-break: break-all; white-space: normal; }
</style>
