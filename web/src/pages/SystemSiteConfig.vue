<template>
  <div class="wrap">
    <el-card shadow="never">
      <div class="header">
        <div class="title">{{ siteId===0 ? '系统配置' : '站点配置' }}</div>
        <div class="sub" v-if="siteId!==0">站点ID：{{ siteId }}</div>
      </div>
      <el-tabs v-model="active">
        <el-tab-pane label="存储配置" name="storage">
          <el-form :model="storage" label-width="120px" class="form">
            <el-form-item label="存储服务"><el-input v-model="storage.provider" placeholder="如：b2、s3、oss" /></el-form-item>
            <el-form-item label="访问密钥ID"><el-input v-model="storage.access_key_id" /></el-form-item>
            <el-form-item label="密钥"><el-input v-model="storage.secret_key" type="password" /></el-form-item>
            <el-form-item label="存储桶名称"><el-input v-model="storage.bucket_name" /></el-form-item>
            <el-form-item label="存储区域"><el-input v-model="storage.bucket_region" /></el-form-item>
            <el-form-item label="接口地址（Endpoint）"><el-input v-model="storage.endpoint" placeholder="如：https://s3.us-east-005.backblazeb2.com" /></el-form-item>
            <el-form-item label="CDN 基础地址"><el-input v-model="storage.base_url" placeholder="如：https://cdn.example.com" /></el-form-item>
            <el-form-item label="允许后缀"><el-input v-model="storage.allowed_suffix" placeholder="如：jpg,png,webp,mp4" /></el-form-item>
            <el-form-item label="启用"><el-switch v-model="storage.is_active" :active-value="1" :inactive-value="0" /></el-form-item>
            <div class="actions"><el-button type="primary" :loading="saving" @click="saveStorage">保存</el-button></div>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="邮件配置" name="email">
          <el-form v-if="email" :model="email" label-width="140px" class="form">
            <el-form-item label="服务商"><el-input v-model="email.provider" /></el-form-item>
            <el-form-item label="区域"><el-input v-model="email.region" /></el-form-item>
            <el-form-item label="访问密钥"><el-input v-model="email.access_key" /></el-form-item>
            <el-form-item label="密钥"><el-input v-model="email.secret_key" type="password" /></el-form-item>
            <el-form-item label="发件邮箱"><el-input v-model="email.from_address" /></el-form-item>
            <el-form-item label="发件人名称"><el-input v-model="email.from_name" /></el-form-item>
            <el-form-item label="启用"><el-switch v-model="email.is_active" :active-value="1" :inactive-value="0" /></el-form-item>
            <div class="actions"><el-button type="primary" :loading="savingEmail" @click="onSaveEmail">保存</el-button></div>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="搜索配置" name="search">
          <el-form v-if="search" :model="search" label-width="140px" class="form">
            <el-form-item v-for="k in keys(search)" :key="k" :label="labelOf(k)">
              <el-switch v-if="k==='is_active'" v-model="search[k]" :active-value="1" :inactive-value="0" />
              <el-input v-else v-model="search[k]" />
            </el-form-item>
            <div class="actions"><el-button type="primary" :loading="savingSearch" @click="onSaveSearch">保存</el-button></div>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="AI配置" name="ai">
          <el-form v-if="ai" :model="ai" label-width="140px" class="form">
            <el-form-item v-for="k in keys(ai)" :key="k" :label="labelOf(k)">
              <el-switch v-if="k==='is_active'" v-model="ai[k]" :active-value="1" :inactive-value="0" />
              <el-input v-else v-model="ai[k]" />
            </el-form-item>
            <div class="actions"><el-button type="primary" :loading="savingAi" @click="onSaveAi">保存</el-button></div>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="推荐配置" name="recommendation">
          <el-form v-if="recommendation" :model="recommendation" label-width="140px" class="form">
            <el-form-item v-for="k in keys(recommendation)" :key="k" :label="labelOf(k)">
              <el-switch v-if="k==='is_active'" v-model="recommendation[k]" :active-value="1" :inactive-value="0" />
              <el-input v-else v-model="recommendation[k]" />
            </el-form-item>
            <div class="actions"><el-button type="primary" :loading="savingRecommendation" @click="onSaveRecommendation">保存</el-button></div>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="客服配置" name="customerService">
          <el-form v-if="customerService" :model="customerService" label-width="140px" class="form">
            <el-form-item v-for="k in keys(customerService)" :key="k" :label="labelOf(k)">
              <el-switch v-if="k==='is_active'" v-model="customerService[k]" :active-value="1" :inactive-value="0" />
              <el-input v-else v-model="customerService[k]" />
            </el-form-item>
            <div class="actions"><el-button type="primary" :loading="savingCustomerService" @click="onSaveCustomerService">保存</el-button></div>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="评论审核" name="commentReview">
          <el-form v-if="commentReview" :model="commentReview" label-width="140px" class="form">
            <el-form-item label="启用">
              <el-switch v-model="commentReview.enabled" :active-value="1" :inactive-value="0" />
            </el-form-item>
            <el-form-item label="需要人工审核">
              <el-switch v-model="commentReview.require_approval" :active-value="1" :inactive-value="0" />
            </el-form-item>
            <el-form-item label="最大长度">
              <el-input-number v-model="commentReview.max_length" :min="1" />
            </el-form-item>
            <el-form-item label="每分钟限制">
              <el-input-number v-model="commentReview.max_per_minute" :min="1" />
            </el-form-item>
            <el-form-item label="违禁词列表">
              <el-input 
                v-model="commentReview.forbidden_words_str" 
                type="textarea" 
                :rows="4" 
                placeholder="请输入违禁词，支持汉字、字母、数字、下划线及破折号。多个词可用逗号、换行或空格分隔。"
                @input="onForbiddenWordsInput"
              />
            </el-form-item>
            <div class="actions"><el-button type="primary" :loading="savingCommentReview" @click="onSaveCommentReview">保存</el-button></div>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="Telegram 审核" name="telegramAudit">
          <el-form v-if="telegramAudit" :model="telegramAudit" label-width="140px" class="form">
            <el-form-item v-for="k in keys(telegramAudit)" :key="k" :label="labelOf(k)">
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
const active = ref('email')
const site = ref<any>(null)
const saving = ref(false)
const savingSearch = ref(false)
const savingAi = ref(false)
const savingRecommendation = ref(false)
const savingCustomerService = ref(false)
const savingCommentReview = ref(false)
const savingTelegramAudit = ref(false)
const savingEmail = ref(false)
const storage = ref<any>({ site_id: siteId, provider: '', access_key_id: '', secret_key: '', bucket_name: '', bucket_region: '', endpoint: '', base_url: '', allowed_suffix: '', is_active: 1 })
const email = ref<any>(null)
const search = ref<any>(null)
const ai = ref<any>(null)
const recommendation = ref<any>(null)
const customerService = ref<any>(null)
const commentReview = ref<any>(null)
const telegramAudit = ref<any>(null)

function defaultEmailConfig(){
  return { provider: '', region: '', access_key: '', secret_key: '', from_address: '', from_name: '', is_active: 1 }
}
function defaultSearchConfig(){
  return { provider: '', base_url: '', index_name: '', api_key: '', is_active: 1 }
}
function defaultAiConfig(){
  return { provider: '', base_url: '', api_key: '', model: '', is_active: 1 }
}
function defaultRecommendationConfig(){
  return { provider: '', base_url: '', api_key: '', is_active: 1 }
}
function defaultCustomerServiceConfig(){
  return { provider: '', base_url: '', api_key: '', webhook_url: '', is_active: 1 }
}
function defaultCommentReviewConfig(){
  return { enabled: 1, require_approval: 1, max_length: 500, max_per_minute: 5, forbidden_words_json: [], forbidden_words_str: '' }
}
function defaultTelegramAuditConfig(){
  return { bot_token: '', chat_id: '', is_active: 1 }
}

function keys(obj:any){
  const h = ['id','site_id','updated_at']
  return Object.keys(obj || {}).filter((k)=>!h.includes(k))
}

function labelOf(k: string){
  const map: Record<string,string> = {
    provider: '服务商',
    base_url: '基础地址',
    index_name: '索引名称',
    api_key: '访问密钥',
    model: '模型',
    webhook_url: '回调地址',
    bot_token: 'Bot Token',
    chat_id: 'Chat ID',
    is_active: '启用',
    region: '区域',
    access_key: '访问密钥',
    secret_key: '密钥',
    from_address: '发件邮箱',
    from_name: '发件人名称',
  }
  return map[k] || k
}
async function load() {
  site.value = siteId === 0 ? null : await fetchSiteDetail(siteId)
  const cfg = await fetchStorageConfigBySite(siteId)
  if (cfg) {
    storage.value = {
      site_id: siteId,
      provider: cfg.provider || '',
      access_key_id: cfg.access_key_id || '',
      secret_key: cfg.secret_key || '',
      bucket_name: cfg.bucket_name || '',
      bucket_region: cfg.bucket_region || '',
      endpoint: cfg.endpoint || '',
      base_url: cfg.base_url || '',
      allowed_suffix: cfg.allowed_suffix || '',
      is_active: Number(cfg.is_active ?? 1),
    }
  }
  // Pre-initialize configs for visibility
  email.value = defaultEmailConfig()
  search.value = defaultSearchConfig()
  ai.value = defaultAiConfig()
  recommendation.value = defaultRecommendationConfig()
  customerService.value = defaultCustomerServiceConfig()
  commentReview.value = defaultCommentReviewConfig()
  telegramAudit.value = defaultTelegramAuditConfig()
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
        endpoint: cfg.endpoint || '',
        base_url: cfg.base_url || '',
        allowed_suffix: cfg.allowed_suffix || '',
        is_active: Number(cfg.is_active ?? 1),
      }
    }
  }
  if (n === 'email') {
    const cfg = await fetchEmailConfigBySite(siteId)
    email.value = cfg || defaultEmailConfig()
  } else if (n === 'search') {
    const cfg = await fetchSearchConfigBySite(siteId)
    search.value = cfg || defaultSearchConfig()
  } else if (n === 'ai') {
    const cfg = await fetchAiConfigBySite(siteId)
    ai.value = cfg || defaultAiConfig()
  } else if (n === 'recommendation') {
    const cfg = await fetchRecommendationConfigBySite(siteId)
    recommendation.value = cfg || defaultRecommendationConfig()
  } else if (n === 'customerService') {
    const cfg = await fetchCustomerServiceConfigBySite(siteId)
    customerService.value = cfg || defaultCustomerServiceConfig()
  } else if (n === 'commentReview') {
    const cfg = await fetchCommentReviewConfigBySite(siteId)
    if (cfg) {
      commentReview.value = {
        enabled: cfg.enabled ?? 1,
        require_approval: cfg.require_approval ?? 1,
        max_length: cfg.max_length ?? 500,
        max_per_minute: cfg.max_per_minute ?? 5,
        forbidden_words_json: cfg.forbidden_words_json || [],
        forbidden_words_str: (cfg.forbidden_words_json || []).join(',')
      }
    } else {
      commentReview.value = defaultCommentReviewConfig()
    }
  } else if (n === 'telegramAudit') {
    const cfg = await fetchTelegramAuditConfigBySite(siteId)
    telegramAudit.value = cfg || defaultTelegramAuditConfig()
  }
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
  try { 
    savingCommentReview.value = true; 
    const payload = { ...commentReview.value }
    delete payload.forbidden_words_str
    const res = await saveCommentReviewConfig({ site_id: siteId, ...payload }); 
    if (res?.code===200) ElMessage.success('保存成功'); 
    else ElMessage.error(res?.msg||'保存失败') 
  } finally { 
    savingCommentReview.value = false 
  }
}

function onForbiddenWordsInput(val: string) {
  if (!commentReview.value) return
  const arr = val.split(/[,，\n\s]+/).map(s => s.trim()).filter(s => s)
  commentReview.value.forbidden_words_json = arr
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
