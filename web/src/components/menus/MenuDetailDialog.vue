<template>
  <el-dialog
    :model-value="modelValue"
    title="菜单详情"
    width="600px"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <el-descriptions
      :column="cols"
      border
    >
      <el-descriptions-item label="ID">
        {{ detail?.id }}
      </el-descriptions-item>
      <el-descriptions-item label="父级ID">
        {{ detail?.parent_id || '无' }}
      </el-descriptions-item>
      <el-descriptions-item label="名称">
        <span class="name-cell">
          <el-icon
            v-if="menuIcon"
            :size="16"
          >
            <component :is="menuIcon" />
          </el-icon>
          {{ detail?.name }}
        </span>
      </el-descriptions-item>
      <el-descriptions-item label="编码">
        <el-tag size="small">
          {{ detail?.code }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="路由">
        {{ detail?.path || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="后端标识">
        {{ detail?.route || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="图标">
        {{ detail?.icon || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="类型">
        <el-tag
          size="small"
          :type="detail?.type === 'menu' ? 'success' : 'info'"
        >
          {{ detail?.type === 'menu' ? '菜单' : '按钮' }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="显示">
        <el-tag
          size="small"
          :type="detail?.visible === 1 ? '' : 'warning'"
        >
          {{ detail?.visible === 1 ? '显示' : '隐藏' }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="启用">
        <el-tag
          size="small"
          :type="detail?.is_active === 1 ? 'success' : 'danger'"
        >
          {{ detail?.is_active === 1 ? '启用' : '停用' }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="排序">
        {{ detail?.sort_order }}
      </el-descriptions-item>
      <el-descriptions-item label="创建时间">
        {{ detail?.created_at }}
      </el-descriptions-item>
      <el-descriptions-item
        label="更新时间"
        :span="2"
      >
        {{ detail?.updated_at }}
      </el-descriptions-item>
    </el-descriptions>

    <template #footer>
      <el-button @click="$emit('update:modelValue', false)">
        关闭
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import type { MenuNode } from '../../api/menus'
import { resolveMenuIcon } from '../../composables/useMenuForm'

const props = defineProps<{
  modelValue: boolean
  detail: MenuNode | null
}>()

defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const cols = ref(2)

const menuIcon = computed(() => resolveMenuIcon(props.detail?.icon))

function updateCols() {
  cols.value = window.innerWidth < 600 ? 1 : 2
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
.name-cell {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
</style>
