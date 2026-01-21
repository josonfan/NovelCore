<template>
  <el-dialog
    :model-value="visible"
    :title="mode === 'add' ? '新建小说' : '编辑小说'"
    width="560px"
    @update:model-value="$emit('update:visible', $event)"
  >
    <el-form
      :model="formData"
      label-width="100px"
    >
      <el-tabs v-model="currentTab">
        <el-tab-pane
          label="基本信息"
          name="basic"
        >
          <el-form-item label="封面">
            <ImageUploader
              v-model="formData.cover"
              :max-size="2"
              tip="建议尺寸 300×400，不超过 2MB"
            />
          </el-form-item>
          <el-form-item label="标题">
            <el-input
              v-model="formData.title"
              placeholder="请输入小说标题"
            />
          </el-form-item>
          <el-form-item label="作者ID">
            <el-input
              v-model="formData.author_id"
              placeholder="关联前台用户或作者表ID"
            />
          </el-form-item>
          <el-form-item label="作者名称">
            <el-input
              v-model="formData.author_name"
              placeholder="作者名称"
            />
          </el-form-item>
          <el-form-item label="分类">
            <el-select
              v-model="formData.category_id"
              placeholder="选择分类"
              style="width: 180px"
            >
              <el-option
                v-for="c in categories"
                :key="c.id"
                :label="c.name"
                :value="c.id"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="小说简介">
            <el-input
              v-model="formData.intro"
              type="textarea"
              :rows="4"
              placeholder="请输入小说简介"
            />
          </el-form-item>
          <el-form-item label="连载状态">
            <el-select
              v-model="formData.status"
              style="width: 180px"
            >
              <el-option
                :value="0"
                label="连载中"
              />
              <el-option
                :value="1"
                label="已完结"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="R18作品">
            <el-switch
              v-model="formData.is_r18"
              :active-value="1"
              :inactive-value="0"
              active-text="是"
              inactive-text="否"
            />
          </el-form-item>
          <el-form-item label="VIP收费">
            <el-switch
              v-model="formData.is_vip"
              :active-value="1"
              :inactive-value="0"
              active-text="是"
              inactive-text="否"
            />
          </el-form-item>
          <el-form-item label="审核状态">
            <el-tag
              :type="auditTagType(formData.audit_status)"
              size="small"
            >
              {{ auditLabel(formData.audit_status) }}
            </el-tag>
          </el-form-item>
        </el-tab-pane>
        <el-tab-pane
          label="SEO"
          name="seo"
        >
          <el-form-item label="SEO标题">
            <el-input v-model="formData.seo_title" />
          </el-form-item>
          <el-form-item label="SEO关键字">
            <el-input v-model="formData.seo_keywords" />
          </el-form-item>
          <el-form-item label="SEO描述">
            <el-input
              v-model="formData.seo_description"
              type="textarea"
            />
          </el-form-item>
        </el-tab-pane>
      </el-tabs>
    </el-form>
    <template #footer>
      <el-button
        :disabled="submitting"
        @click="$emit('update:visible', false)"
      >
        取消
      </el-button>
      <el-button
        type="primary"
        :loading="submitting"
        :disabled="submitting"
        @click="$emit('save')"
      >
        保存
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { NovelFormData } from '../../composables/useNovelForm'
import ImageUploader from '../ImageUploader.vue'

const props = defineProps<{
  visible: boolean
  mode: 'add' | 'edit'
  activeTab: string
  categories: Array<{ id: number | string; name: string }>
  submitting?: boolean
}>()

const formData = defineModel<NovelFormData>('form', { required: true })

const emit = defineEmits<{
  'update:visible': [value: boolean]
  'update:activeTab': [value: string]
  'save': []
}>()

const currentTab = computed({
  get: () => props.activeTab,
  set: (v) => emit('update:activeTab', v),
})

function auditLabel(s: unknown) {
  const v = Number(s ?? 0)
  if (v === 1) return '已通过'
  if (v === 2) return '已拒绝'
  if (v === 3) return '已下线'
  return '待审'
}

function auditTagType(s: unknown) {
  const v = Number(s ?? 0)
  if (v === 1) return 'success'
  if (v === 2) return 'danger'
  if (v === 3) return 'info'
  return 'warning'
}
</script>
