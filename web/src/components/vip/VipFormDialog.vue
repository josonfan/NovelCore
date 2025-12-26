<template>
  <el-dialog
    :model-value="visible"
    :title="mode === 'add' ? '新建VIP套餐' : '编辑VIP套餐'"
    width="600px"
    @update:model-value="$emit('update:visible', $event)"
  >
    <el-form
      :model="form"
      label-width="100px"
    >
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="套餐名称">
            <el-input
              :model-value="form.name"
              @update:model-value="updateForm('name', $event)"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="天数">
            <el-input-number
              :model-value="form.days"
              :min="1"
              style="width: 100%"
              @update:model-value="updateForm('days', $event)"
            />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="价格">
            <el-input
              :model-value="form.price"
              @update:model-value="updateForm('price', $event)"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="原价">
            <el-input
              :model-value="form.old_price"
              placeholder="划线价"
              @update:model-value="updateForm('old_price', $event)"
            />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row :gutter="20">
        <el-col :span="8">
          <el-form-item label="状态">
            <el-switch
              :model-value="form.status"
              :active-value="1"
              :inactive-value="0"
              @update:model-value="updateForm('status', $event)"
            />
          </el-form-item>
        </el-col>
        <el-col :span="8">
          <el-form-item label="热门">
            <el-switch
              :model-value="form.is_hot"
              :active-value="1"
              :inactive-value="2"
              @update:model-value="updateForm('is_hot', $event)"
            />
          </el-form-item>
        </el-col>
        <el-col :span="8">
          <el-form-item label="排序">
            <el-input-number
              :model-value="form.sort"
              :min="0"
              style="width: 100%"
              @update:model-value="updateForm('sort', $event)"
            />
          </el-form-item>
        </el-col>
      </el-row>
      <el-form-item label="描述">
        <el-input
          type="textarea"
          :rows="3"
          :model-value="form.descript"
          placeholder="套餐描述信息"
          @update:model-value="updateForm('descript', $event)"
        />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="$emit('update:visible', false)">
        取消
      </el-button>
      <el-button
        type="primary"
        @click="$emit('save')"
      >
        保存
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import type { VipFormData } from '../../composables/useVipForm'

const props = defineProps<{
  visible: boolean
  mode: 'add' | 'edit'
  form: VipFormData
}>()

const emit = defineEmits<{
  'update:visible': [value: boolean]
  'update:form': [value: VipFormData]
  'save': []
}>()

function updateForm<K extends keyof VipFormData>(key: K, value: VipFormData[K]) {
  emit('update:form', { ...props.form, [key]: value })
}
</script>
