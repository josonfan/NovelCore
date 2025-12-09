<template>
  <div class="wrap">
    <el-card shadow="never">
      <div class="header">
        <div class="title">站点配置</div>
        <div class="sub">站点ID：{{ siteId }}</div>
      </div>
      <el-tabs v-model="active">
        <el-tab-pane label="基础信息" name="basic">
          <el-descriptions :column="2" border v-if="site">
            <el-descriptions-item label="名称">{{ site.name }}</el-descriptions-item>
            <el-descriptions-item label="编码">{{ site.code }}</el-descriptions-item>
            <el-descriptions-item label="基础API">{{ site.base_api_url }}</el-descriptions-item>
            <el-descriptions-item label="主域名">{{ site.primary_domain }}</el-descriptions-item>
            <el-descriptions-item label="启用">{{ site.is_active }}</el-descriptions-item>
            <el-descriptions-item label="备注">{{ site.remark }}</el-descriptions-item>
          </el-descriptions>
        </el-tab-pane>
        <el-tab-pane label="存储配置" name="storage">
          <el-form :model="storage" label-width="120px" class="form">
            <el-form-item label="Provider"><el-input v-model="storage.provider" placeholder="如：b2、s3、oss" /></el-form-item>
            <el-form-item label="Access Key Id"><el-input v-model="storage.access_key_id" /></el-form-item>
            <el-form-item label="Secret Key"><el-input v-model="storage.secret_key" type="password" /></el-form-item>
            <el-form-item label="Bucket Name"><el-input v-model="storage.bucket_name" /></el-form-item>
            <el-form-item label="Bucket Region"><el-input v-model="storage.bucket_region" /></el-form-item>
            <el-form-item label="CDN Base URL"><el-input v-model="storage.base_url" placeholder="如：https://cdn.example.com" /></el-form-item>
            <el-form-item label="启用"><el-switch v-model="storage.is_active" :active-value="1" :inactive-value="0" /></el-form-item>
            <div class="actions"><el-button type="primary" :loading="saving" @click="saveStorage">保存</el-button></div>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="邮件配置" name="email">
          <el-form v-if="email" :model="email" label-width="140px" class="form">
            <el-form-item label="Provider"><el-input v-model="email.provider" /></el-form-item>
            <el-form-item label="Region"><el-input v-model="email.region" /></el-form-item>
            <el-form-item label="Access Key"><el-input v-model="email.access_key" /></el-form-item>
            <el-form-item label="Secret Key"><el-input v-model="email.secret_key" type="password" /></el-form-item>
            <el-form-item label="From Address"><el-input v-model="email.from_address" /></el-form-item>
            <el-form-item label="From Name"><el-input v-model="email.from_name" /></el-form-item>
            <el-form-item label="启用"><el-switch v-model="email.is_active" :active-value="1" :inactive-value="0" /></el-form-item>
            <div class="actions"><el-button type="primary" :loading="savingEmail" @click="onSaveEmail">保存</el-button></div>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="搜索配置" name="search">
          <el-form v-if="search" :model="search" label-width="140px" class="form">
            <el-form-item v-for="k in keys(search)" :key="k" :label="k !== 'is_active' ? k : '启用'">
              <el-switch v-if="k==='is_active'" v-model="search[k]" :active-value="1" :inactive-value="0" />
              <el-input v-else v-model="search[k]" />
            </el-form-item>
            <div class="actions"><el-button type="primary" :loading="savingSearch" @click="onSaveSearch">保存</el-button></div>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="AI配置" name="ai">
          <el-form v-if="ai" :model="ai" label-width="140px" class="form">
            <el-form-item v-for="k in keys(ai)" :key="k" :label="k !== 'is_active' ? k : '启用'">
              <el-switch v-if="k==='is_active'" v-model="ai[k]" :active-value="1" :inactive-value="0" />
              <el-input v-else v-model="ai[k]" />
            </el-form-item>
            <div class="actions"><el-button type="primary" :loading="savingAi" @click="onSaveAi">保存</el-button></div>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="推荐配置" name="recommendation">
          <el-form v-if="recommendation" :model="recommendation" label-width="140px" class="form">
            <el-form-item v-for="k in keys(recommendation)" :key="k" :label="k !== 'is_active' ? k : '启用'">
              <el-switch v-if="k==='is_active'" v-model="recommendation[k]" :active-value="1" :inactive-value="0" />
              <el-input v-else v-model="recommendation[k]" />
            </el-form-item>
            <div class="actions"><el-button type="primary" :loading="savingRecommendation" @click="onSaveRecommendation">保存</el-button></div>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="客服配置" name="customerService">
          <el-form v-if="customerService" :model="customerService" label-width="140px" class="form">
            <el-form-item v-for="k in keys(customerService)" :key="k" :label="k !== 'is_active' ? k : '启用'">
              <el-switch v-if="k==='is_active'" v-model="customerService[k]" :active-value="1" :inactive-value="0" />
              <el-input v-else v-model="customerService[k]" />
            </el-form-item>
            <div class="actions"><el-button type="primary" :loading="savingCustomerService" @click="onSaveCustomerService">保存</el-button></div>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="评论审核" name="commentReview">
          <el-form v-if="commentReview" :model="commentReview" label-width="140px" class="form">
            <el-form-item v-for="k in keys(commentReview)" :key="k" :label="k !== 'is_active' ? k : '启用'">
              <el-switch v-if="k==='is_active'" v-model="commentReview[k]" :active-value="1" :inactive-value="0" />
              <el-input v-else v-model="commentReview[k]" />
            </el-form-item>
            <div class="actions"><el-button type="primary" :loading="savingCommentReview" @click="onSaveCommentReview">保存</el-button></div>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="Telegram 审核" name="telegramAudit">
          <el-form v-if="telegramAudit" :model="telegramAudit" label-width="140px" class="form">
            <el-form-item v-for="k in keys(telegramAudit)" :key="k" :label="k !== 'is_active' ? k : '启用'">
              <el-switch v-if="k==='is_active'" v-model="telegramAudit[k]" :active-value="1" :inactive-value="0" />
              <el-input v-else v-model="telegramAudit[k]" />
            </el-form-item>
            <div class="actions"><el-button type="primary" :loading="savingTelegramAudit" @click="onSaveTelegramAudit">保存</el-button></div>
          </el-form>
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
const props = defineProps<{ siteId?: number | string }>()
import { useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { fetchSiteDetail } from '../api/sites'
import { saveStorageConfig, fetchStorageConfigBySite } from '../api/storageConfig'
import { fetchEmailConfigBySite, fetchSearchConfigBySite, fetchAiConfigBySite, fetchRecommendationConfigBySite, fetchCustomerServiceConfigBySite, fetchCommentReviewConfigBySite, fetchTelegramAuditConfigBySite, saveSearchConfig, saveAiConfig, saveRecommendationConfig, saveCustomerServiceConfig, saveCommentReviewConfig, saveTelegramAuditConfig, saveEmailConfig } from '../api/siteConfigs'

const route = useRoute()
const siteId = Number((props.siteId ?? route.params.id) as any)
const active = ref('basic')
const site = ref<any>(null)
const saving = ref(false)
const savingSearch = ref(false)
const savingAi = ref(false)
const savingRecommendation = ref(false)
const savingCustomerService = ref(false)
const savingCommentReview = ref(false)
const savingTelegramAudit = ref(false)
const savingEmail = ref(false)
const storage = ref<any>({ site_id: siteId, provider: '', access_key_id: '', secret_key: '', bucket_name: '', bucket_region: '', base_url: '', is_active: 1 })
const email = ref<any>(null)
const search = ref<any>(null)
const ai = ref<any>(null)
const recommendation = ref<any>(null)
const customerService = ref<any>(null)
const commentReview = ref<any>(null)
const telegramAudit = ref<any>(null)

function keys(obj:any){
  const h = ['id','site_id','updated_at']
  return Object.keys(obj || {}).filter((k)=>!h.includes(k))
}

async function load() {
  site.value = await fetchSiteDetail(siteId)
  const cfg = await fetchStorageConfigBySite(siteId)
  if (cfg) {
    storage.value = {
      site_id: siteId,
      provider: cfg.provider || '',
      access_key_id: cfg.access_key_id || '',
      secret_key: cfg.secret_key || '',
      bucket_name: cfg.bucket_name || '',
      bucket_region: cfg.bucket_region || '',
      base_url: cfg.base_url || '',
      is_active: Number(cfg.is_active ?? 1),
    }
  }
}

async function saveStorage() {
  try {
    saving.value = true
    const res = await saveStorageConfig(storage.value)
    if (res?.code === 200) {
      ElMessage.success('保存成功')
    } else {
      ElMessage.error(res?.msg || '保存失败')
    }
  } catch (e: any) {
    const resp = e?.response?.data
    ElMessage.error(resp?.message || resp?.msg || '保存失败')
  } finally {
    saving.value = false
  }
}

onMounted(load)

watch(active, async (n) => {
  if (n === 'storage') {
    const cfg = await fetchStorageConfigBySite(siteId)
    if (cfg) {
      storage.value = {
        site_id: siteId,
        provider: cfg.provider || '',
        access_key_id: cfg.access_key_id || '',
        secret_key: cfg.secret_key || '',
        bucket_name: cfg.bucket_name || '',
        bucket_region: cfg.bucket_region || '',
        base_url: cfg.base_url || '',
        is_active: Number(cfg.is_active ?? 1),
      }
    }
  }
  if (n === 'email' && !email.value) email.value = await fetchEmailConfigBySite(siteId)
  else if (n === 'search' && !search.value) search.value = await fetchSearchConfigBySite(siteId)
  else if (n === 'ai' && !ai.value) ai.value = await fetchAiConfigBySite(siteId)
  else if (n === 'recommendation' && !recommendation.value) recommendation.value = await fetchRecommendationConfigBySite(siteId)
  else if (n === 'customerService' && !customerService.value) customerService.value = await fetchCustomerServiceConfigBySite(siteId)
  else if (n === 'commentReview' && !commentReview.value) commentReview.value = await fetchCommentReviewConfigBySite(siteId)
  else if (n === 'telegramAudit' && !telegramAudit.value) telegramAudit.value = await fetchTelegramAuditConfigBySite(siteId)
})

async function onSaveSearch() {
  try { savingSearch.value = true; const res = await saveSearchConfig({ site_id: siteId, ...search.value }); if (res?.code===200) ElMessage.success('保存成功'); else ElMessage.error(res?.msg||'保存失败') } finally { savingSearch.value = false }
}
async function onSaveAi() {
  try { savingAi.value = true; const res = await saveAiConfig({ site_id: siteId, ...ai.value }); if (res?.code===200) ElMessage.success('保存成功'); else ElMessage.error(res?.msg||'保存失败') } finally { savingAi.value = false }
}
async function onSaveRecommendation() {
  try { savingRecommendation.value = true; const res = await saveRecommendationConfig({ site_id: siteId, ...recommendation.value }); if (res?.code===200) ElMessage.success('保存成功'); else ElMessage.error(res?.msg||'保存失败') } finally { savingRecommendation.value = false }
}
async function onSaveCustomerService() {
  try { savingCustomerService.value = true; const res = await saveCustomerServiceConfig({ site_id: siteId, ...customerService.value }); if (res?.code===200) ElMessage.success('保存成功'); else ElMessage.error(res?.msg||'保存失败') } finally { savingCustomerService.value = false }
}
async function onSaveCommentReview() {
  try { savingCommentReview.value = true; const res = await saveCommentReviewConfig({ site_id: siteId, ...commentReview.value }); if (res?.code===200) ElMessage.success('保存成功'); else ElMessage.error(res?.msg||'保存失败') } finally { savingCommentReview.value = false }
}
async function onSaveTelegramAudit() {
  try { savingTelegramAudit.value = true; const res = await saveTelegramAuditConfig({ site_id: siteId, ...telegramAudit.value }); if (res?.code===200) ElMessage.success('保存成功'); else ElMessage.error(res?.msg||'保存失败') } finally { savingTelegramAudit.value = false }
}

async function onSaveEmail() {
  try { savingEmail.value = true; const res = await saveEmailConfig({ site_id: siteId, ...email.value }); if (res?.code===200) ElMessage.success('保存成功'); else ElMessage.error(res?.msg||'保存失败') } finally { savingEmail.value = false }
}
</script>

<style scoped>
.wrap { display: grid; gap: 12px; }
.header { display:flex; align-items:center; justify-content: space-between; margin-bottom: 8px; }
.title { font-weight: 700; font-size: 18px; }
.sub { color: var(--nc-muted); }
.form { max-width: 640px; margin-top: 8px; }
.actions { margin-top: 8px; }
.muted { color: var(--nc-muted); }
</style>
