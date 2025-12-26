<template>
  <div class="wrap">
    <el-card shadow="never">
      <div class="header">
        <div class="title">
          {{ siteId === 0 ? '系统配置' : '站点配置' }}
        </div>
        <div
          v-if="siteId !== 0"
          class="sub"
        >
          站点ID：{{ siteId }}
        </div>
      </div>

      <el-tabs v-model="activeTab">
        <el-tab-pane
          label="存储配置"
          name="storage"
        >
          <StorageConfigForm
            v-if="activeTab === 'storage'"
            :site-id="siteId"
          />
        </el-tab-pane>

        <el-tab-pane
          label="邮件配置"
          name="email"
        >
          <EmailConfigForm
            v-if="activeTab === 'email'"
            :site-id="siteId"
          />
        </el-tab-pane>

        <el-tab-pane
          label="搜索配置"
          name="search"
        >
          <SearchConfigForm
            v-if="activeTab === 'search'"
            :site-id="siteId"
          />
        </el-tab-pane>

        <el-tab-pane
          label="AI配置"
          name="ai"
        >
          <AiConfigForm
            v-if="activeTab === 'ai'"
            :site-id="siteId"
          />
        </el-tab-pane>

        <el-tab-pane
          label="推荐配置"
          name="recommendation"
        >
          <RecommendationConfigForm
            v-if="activeTab === 'recommendation'"
            :site-id="siteId"
          />
        </el-tab-pane>

        <el-tab-pane
          label="客服配置"
          name="customerService"
        >
          <CustomerServiceConfigForm
            v-if="activeTab === 'customerService'"
            :site-id="siteId"
          />
        </el-tab-pane>

        <el-tab-pane
          label="评论审核"
          name="commentReview"
        >
          <CommentReviewConfigForm
            v-if="activeTab === 'commentReview'"
            :site-id="siteId"
          />
        </el-tab-pane>

        <el-tab-pane
          label="Telegram 审核"
          name="telegramAudit"
        >
          <TelegramAuditConfigForm
            v-if="activeTab === 'telegramAudit'"
            :site-id="siteId"
          />
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import {
  StorageConfigForm,
  EmailConfigForm,
  SearchConfigForm,
  AiConfigForm,
  RecommendationConfigForm,
  CustomerServiceConfigForm,
  CommentReviewConfigForm,
  TelegramAuditConfigForm,
} from '../components/site-config'

const props = defineProps<{
  siteId?: number | string
}>()

const route = useRoute()
const siteId = Number(props.siteId ?? route.params.id ?? 0)
const activeTab = ref('storage')
</script>

<style scoped>
.wrap {
  display: grid;
  gap: 12px;
}

.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.title {
  font-weight: 700;
  font-size: 18px;
}

.sub {
  color: var(--nc-muted);
}
</style>
