<template>
  <el-dialog
    :model-value="modelValue"
    :title="mode === 'add' ? '新建菜单' : '编辑菜单'"
    width="560px"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <el-form
      :model="form"
      label-width="100px"
    >
      <el-form-item label="上级菜单">
        <ParentEditSelect
          ref="parentSelectRef"
          v-model="form.parent_id"
          :data="treeSelectData"
          :props="treeProps"
          placeholder="请选择上级菜单"
        />
      </el-form-item>

      <el-form-item
        label="名称"
        required
      >
        <el-input
          v-model="form.name"
          placeholder="请输入菜单名称"
        />
      </el-form-item>

      <el-form-item
        label="编码"
        required
      >
        <el-input
          v-model="form.code"
          placeholder="唯一编码，如 system-menu"
        />
      </el-form-item>

      <el-form-item label="路由">
        <el-input
          v-model="form.path"
          placeholder="前端路由路径，如 /system/menu"
        />
      </el-form-item>

      <el-form-item label="后端标识">
        <el-input
          v-model="form.route"
          placeholder="后端路由标识"
        />
      </el-form-item>

      <el-form-item label="图标">
        <div class="icon-field">
          <el-input
            v-model="form.icon"
            placeholder="请选择图标"
            readonly
          />
          <el-popover
            placement="bottom"
            :width="480"
            trigger="click"
          >
            <template #reference>
              <el-button>选择</el-button>
            </template>
            <IconPicker v-model="form.icon" />
          </el-popover>
          <el-icon
            v-if="previewIcon"
            :size="18"
            class="icon-preview"
          >
            <component :is="previewIcon" />
          </el-icon>
        </div>
      </el-form-item>

      <el-form-item label="类型">
        <el-radio-group v-model="form.type">
          <el-radio value="menu">
            菜单
          </el-radio>
          <el-radio value="button">
            按钮
          </el-radio>
        </el-radio-group>
      </el-form-item>

      <el-form-item label="显示">
        <el-switch
          v-model="form.visible"
          :active-value="1"
          :inactive-value="0"
        />
        <span class="field-hint">是否在侧边栏显示</span>
      </el-form-item>

      <el-form-item label="启用">
        <el-switch
          v-model="form.is_active"
          :active-value="1"
          :inactive-value="0"
        />
      </el-form-item>

      <el-form-item label="排序">
        <el-input-number
          v-model="form.sort_order"
          :min="0"
          :step="1"
        />
        <span class="field-hint">数字越小越靠前</span>
      </el-form-item>
    </el-form>

    <template #footer>
      <el-button @click="$emit('update:modelValue', false)">
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
import { computed, ref, watch, nextTick } from 'vue'
import ParentEditSelect from '../ParentEditSelect.vue'
import IconPicker from '../IconPicker.vue'
import type { MenuNode } from '../../api/menus'
import type { MenuFormData } from '../../composables/useMenuForm'
import { buildTreeSelectData } from '../../composables/useMenuManage'

const props = defineProps<{
  modelValue: boolean
  mode: 'add' | 'edit'
  form: MenuFormData
  previewIcon: unknown
  options: MenuNode[]
}>()

defineEmits<{
  'update:modelValue': [value: boolean]
  'save': []
}>()

const parentSelectRef = ref()
const treeSelectData = computed(() => buildTreeSelectData(props.options))

const treeProps = {
  value: 'id',
  label: 'label',
  children: 'children',
}

// 打开时自动聚焦
watch(
  () => props.modelValue,
  (val) => {
    if (val) {
      nextTick(() => {
        parentSelectRef.value?.focus?.()
      })
    }
  }
)
</script>

<style scoped>
.icon-field {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
}

.icon-preview {
  color: var(--nc-primary);
}

.field-hint {
  margin-left: 8px;
  font-size: 12px;
  color: var(--nc-muted);
}
</style>
