<template>
  <div class="wrap">
    <SiteToolbar
      v-model="kw"
      :loading="loading"
      :total="total"
      @refresh="load"
      @add="openAdd"
    />

    <SiteTable
      :data="filtered"
      :loading="loading"
      :total="total"
      :current-page="page"
      :page-size="limit"
      @detail="onDetail"
      @edit="openEdit"
      @toggle="handleToggle"
      @config="onConfig"
      @init="handleInit"
      @delete="handleDelete"
      @page-change="onPage"
      @size-change="onSize"
    />

    <SiteFormDialog
      v-model:visible="showForm"
      v-model:form="form"
      :mode="formMode"
      @save="saveForm"
    />

    <SiteDetailDialog
      v-model:visible="showDetail"
      :detail="detail"
      :health="health"
      :health-loading="healthLoading"
      :health-failed="healthFailed"
      :format-time="formatTime"
      @check-health="onCheckHealth"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { SiteToolbar, SiteTable, SiteFormDialog, SiteDetailDialog } from '../components/sites'
import { fetchSiteDetail, type Site } from '../api/sites'
import { useSiteList } from '../composables/useSiteList'
import { useSiteForm } from '../composables/useSiteForm'
import { useSiteHealth } from '../composables/useSiteHealth'

const router = useRouter()

// 列表逻辑
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
  handleToggle,
  handleDelete,
  handleInit,
} = useSiteList()

// 表单逻辑
const {
  showForm,
  formMode,
  form,
  openAdd,
  openEdit,
  saveForm,
} = useSiteForm(load)

// 健康检查逻辑
const {
  health,
  healthLoading,
  healthFailed,
  formatTime,
  checkHealth,
  stopHealthTimer,
} = useSiteHealth()

// 详情弹窗
const showDetail = ref(false)
const detail = ref<Site | null>(null)

async function onDetail(row: Site) {
  const d = await fetchSiteDetail(row.id)
  detail.value = d
  health.value = null
  healthFailed.value = false
  showDetail.value = true
}

function onConfig(row: Site) {
  router.push({ name: 'system-site-config', params: { id: row.id } })
}

function onCheckHealth() {
  checkHealth(detail.value, false)
}

// 监听详情弹窗状态，自动检测健康状态
let healthTimer: ReturnType<typeof setInterval> | null = null

watch(showDetail, (v) => {
  if (v) {
    checkHealth(detail.value, true)
    if (healthTimer) clearInterval(healthTimer)
    healthTimer = setInterval(() => checkHealth(detail.value, true), 5000)
  } else {
    if (healthTimer) {
      clearInterval(healthTimer)
      healthTimer = null
    }
    stopHealthTimer()
  }
})

onUnmounted(() => {
  if (healthTimer) {
    clearInterval(healthTimer)
    healthTimer = null
  }
})

onMounted(load)
</script>

<style scoped>
.wrap {
  display: grid;
  gap: 12px;
}
</style>
