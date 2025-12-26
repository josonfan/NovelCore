<template>
  <el-card
    shadow="hover"
    class="table-card"
  >
    <el-table
      v-loading="loading"
      :data="data"
      border
      size="small"
      stripe
      highlight-current-row
    >
      <el-table-column
        prop="id"
        label="ID"
        width="80"
      />
      <el-table-column
        prop="novel_id"
        label="小说ID"
        width="120"
      />
      <el-table-column
        prop="title"
        label="标题"
        min-width="220"
      />
      <el-table-column
        prop="index"
        label="序号"
        width="120"
      />
      <el-table-column
        prop="word_count"
        label="字数"
        width="120"
      />
      <el-table-column
        prop="updated_at"
        label="更新时间"
        min-width="160"
      />
      <el-table-column
        label="操作"
        width="320"
        fixed="right"
      >
        <template #default="{ row }">
          <template
            v-for="btn in actionButtonsRow"
            :key="btn.id"
          >
            <el-button
              :type="resolveBtnType(btn)"
              link
              @click="$emit('action-row', btn, row)"
            >
              <el-icon
                v-if="resolveIcon(btn.icon)"
                :size="16"
                style="margin-right: 6px"
              >
                <component :is="resolveIcon(btn.icon)" />
              </el-icon>
              {{ btn.name }}
            </el-button>
          </template>
          <el-button
            type="primary"
            link
            @click="$emit('edit', row)"
          >
            编辑
          </el-button>
          <el-button
            type="success"
            link
            @click="$emit('view-content', row)"
          >
            查看内容
          </el-button>
          <el-button
            type="danger"
            link
            @click="$emit('delete', row)"
          >
            删除
          </el-button>
        </template>
      </el-table-column>
    </el-table>
    <div class="pager">
      <el-pagination
        background
        layout="prev, pager, next, jumper, sizes, total"
        :page-size="pageSize"
        :current-page="currentPage"
        :total="total"
        :page-sizes="[10, 20, 50]"
        @current-change="$emit('page-change', $event)"
        @size-change="$emit('size-change', $event)"
      />
    </div>
  </el-card>
</template>

<script setup lang="ts">
import type { Chapter } from '../../api/chapters'
import type { ActionButton } from '../../composables/useActionButtons'
import { useActionButtons } from '../../composables/useActionButtons'

defineProps<{
  data: Chapter[]
  loading: boolean
  total: number
  currentPage: number
  pageSize: number
  actionButtonsRow: ActionButton[]
}>()

defineEmits<{
  'action-row': [btn: ActionButton, row: Chapter]
  'edit': [row: Chapter]
  'view-content': [row: Chapter]
  'delete': [row: Chapter]
  'page-change': [page: number]
  'size-change': [size: number]
}>()

const { resolveIcon, resolveBtnType } = useActionButtons()
</script>

<style scoped>
.pager {
  display: flex;
  justify-content: flex-end;
  padding-top: 12px;
}
</style>
