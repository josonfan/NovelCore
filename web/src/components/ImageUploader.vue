<template>
  <div class="image-uploader" :class="{ 'is-multiple': multiple, 'is-disabled': disabled }">
    <!-- 已上传的图片列表 -->
    <div v-if="multiple" class="upload-list">
      <div
        v-for="(img, idx) in imageList"
        :key="img.url || idx"
        class="upload-item"
      >
        <el-image
          :src="img.url"
          fit="cover"
          class="upload-preview"
          :preview-src-list="previewList"
          :initial-index="idx"
        />
        <div class="upload-item-actions">
          <el-icon class="action-icon" @click="handlePreview(idx)"><ZoomIn /></el-icon>
          <el-icon v-if="!disabled" class="action-icon" @click="handleRemove(idx)"><Delete /></el-icon>
        </div>
      </div>

      <!-- 上传中的项 -->
      <div v-for="(item, idx) in uploadingList" :key="'uploading-' + idx" class="upload-item is-uploading">
        <el-progress type="circle" :percentage="item.percent" :width="60" />
      </div>

      <!-- 添加按钮 -->
      <div
        v-if="!disabled && (!limit || imageList.length + uploadingList.length < limit)"
        class="upload-trigger"
        @click="triggerUpload"
        @dragover.prevent="onDragOver"
        @dragleave.prevent="onDragLeave"
        @drop.prevent="onDrop"
        :class="{ 'is-dragover': isDragover }"
      >
        <el-icon class="upload-icon"><Plus /></el-icon>
        <span class="upload-text">{{ uploadText }}</span>
      </div>
    </div>

    <!-- 单图模式 -->
    <div v-else class="upload-single">
      <div
        v-if="singleImage"
        class="upload-item"
      >
        <el-image
          :src="singleImage"
          fit="cover"
          class="upload-preview"
          :preview-src-list="[singleImage]"
        />
        <div class="upload-item-actions">
          <el-icon class="action-icon" @click="handlePreviewSingle"><ZoomIn /></el-icon>
          <el-icon v-if="!disabled" class="action-icon" @click="handleRemoveSingle"><Delete /></el-icon>
        </div>
      </div>

      <!-- 上传中 -->
      <div v-else-if="uploadingList.length > 0" class="upload-item is-uploading">
        <el-progress type="circle" :percentage="uploadingList[0]?.percent || 0" :width="60" />
      </div>

      <!-- 上传触发器 -->
      <div
        v-else-if="!disabled"
        class="upload-trigger"
        @click="triggerUpload"
        @dragover.prevent="onDragOver"
        @dragleave.prevent="onDragLeave"
        @drop.prevent="onDrop"
        :class="{ 'is-dragover': isDragover }"
      >
        <el-icon class="upload-icon"><Plus /></el-icon>
        <span class="upload-text">{{ uploadText }}</span>
      </div>
    </div>

    <!-- 隐藏的文件输入 -->
    <input
      ref="fileInputRef"
      type="file"
      :accept="accept"
      :multiple="multiple"
      style="display: none"
      @change="handleFileChange"
    />

    <!-- 提示文字 -->
    <div v-if="tip" class="upload-tip">{{ tip }}</div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Plus, Delete, ZoomIn } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { uploadFile, type UploadResult } from '../api/upload'

export interface ImageItem {
  url: string
  key?: string
}

interface UploadingItem {
  file: File
  percent: number
}

const props = withDefaults(
  defineProps<{
    /** 单图模式下的图片URL */
    modelValue?: string | ImageItem[]
    /** 是否多图模式 */
    multiple?: boolean
    /** 最大上传数量（多图模式） */
    limit?: number
    /** 接受的文件类型 */
    accept?: string
    /** 最大文件大小（MB） */
    maxSize?: number
    /** 是否禁用 */
    disabled?: boolean
    /** 上传按钮文字 */
    uploadText?: string
    /** 提示文字 */
    tip?: string
  }>(),
  {
    multiple: false,
    accept: 'image/*',
    maxSize: 10,
    disabled: false,
    uploadText: '上传图片',
  }
)

const emit = defineEmits<{
  'update:modelValue': [value: string | ImageItem[]]
  'success': [result: UploadResult]
  'error': [error: Error]
}>()

const fileInputRef = ref<HTMLInputElement>()
const uploadingList = ref<UploadingItem[]>([])
const isDragover = ref(false)

// 多图列表
const imageList = computed<ImageItem[]>(() => {
  if (!props.multiple) return []
  if (Array.isArray(props.modelValue)) return props.modelValue
  return []
})

// 单图URL
const singleImage = computed(() => {
  if (props.multiple) return ''
  if (typeof props.modelValue === 'string') return props.modelValue
  return ''
})

// 预览列表
const previewList = computed(() => imageList.value.map((img) => img.url))

// 触发文件选择
function triggerUpload() {
  fileInputRef.value?.click()
}

// 文件选择变化
function handleFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  const files = Array.from(input.files || [])
  if (files.length) {
    processFiles(files)
  }
  input.value = ''
}

// 拖拽事件
function onDragOver() {
  isDragover.value = true
}

function onDragLeave() {
  isDragover.value = false
}

function onDrop(e: DragEvent) {
  isDragover.value = false
  const files = Array.from(e.dataTransfer?.files || []).filter((f) =>
    f.type.startsWith('image/')
  )
  if (files.length) {
    processFiles(files)
  }
}

// 处理文件
async function processFiles(files: File[]) {
  // 检查数量限制
  if (props.multiple && props.limit) {
    const remaining = props.limit - imageList.value.length - uploadingList.value.length
    if (files.length > remaining) {
      ElMessage.warning(`最多还能上传 ${remaining} 张图片`)
      files = files.slice(0, remaining)
    }
  } else if (!props.multiple) {
    files = [files[0]]
  }

  // 检查文件大小
  const maxBytes = props.maxSize * 1024 * 1024
  for (const file of files) {
    if (file.size > maxBytes) {
      ElMessage.error(`文件 ${file.name} 超过 ${props.maxSize}MB 限制`)
      return
    }
  }

  // 上传文件
  for (const file of files) {
    const uploadItem: UploadingItem = { file, percent: 0 }
    uploadingList.value.push(uploadItem)

    try {
      const result = await uploadFile(file, (percent) => {
        uploadItem.percent = percent
      })

      // 上传成功
      if (props.multiple) {
        const newList = [...imageList.value, { url: result.url, key: result.key }]
        emit('update:modelValue', newList)
      } else {
        emit('update:modelValue', result.url)
      }
      emit('success', result)
    } catch (err) {
      ElMessage.error((err as Error).message || '上传失败')
      emit('error', err as Error)
    } finally {
      const idx = uploadingList.value.indexOf(uploadItem)
      if (idx > -1) uploadingList.value.splice(idx, 1)
    }
  }
}

// 预览图片
function handlePreview(idx: number) {
  // el-image 自带预览功能
}

function handlePreviewSingle() {
  // el-image 自带预览功能
}

// 移除图片
function handleRemove(idx: number) {
  const newList = [...imageList.value]
  newList.splice(idx, 1)
  emit('update:modelValue', newList)
}

function handleRemoveSingle() {
  emit('update:modelValue', '')
}
</script>

<style scoped>
.image-uploader {
  --uploader-size: 104px;
  --uploader-radius: 8px;
}

.upload-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.upload-single {
  display: inline-block;
}

.upload-item {
  position: relative;
  width: var(--uploader-size);
  height: var(--uploader-size);
  border-radius: var(--uploader-radius);
  overflow: hidden;
  border: 1px solid var(--el-border-color);
  background: var(--el-fill-color-lighter);
}

.upload-item.is-uploading {
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-preview {
  width: 100%;
  height: 100%;
}

.upload-item-actions {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: rgba(0, 0, 0, 0.5);
  opacity: 0;
  transition: opacity 0.2s;
}

.upload-item:hover .upload-item-actions {
  opacity: 1;
}

.action-icon {
  color: #fff;
  font-size: 18px;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background 0.2s;
}

.action-icon:hover {
  background: rgba(255, 255, 255, 0.2);
}

.upload-trigger {
  width: var(--uploader-size);
  height: var(--uploader-size);
  border: 1px dashed var(--el-border-color);
  border-radius: var(--uploader-radius);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  cursor: pointer;
  transition: all 0.2s;
  background: var(--el-fill-color-lighter);
}

.upload-trigger:hover,
.upload-trigger.is-dragover {
  border-color: var(--el-color-primary);
  background: var(--el-color-primary-light-9);
}

.upload-icon {
  font-size: 24px;
  color: var(--el-text-color-placeholder);
}

.upload-trigger:hover .upload-icon,
.upload-trigger.is-dragover .upload-icon {
  color: var(--el-color-primary);
}

.upload-text {
  font-size: 12px;
  color: var(--el-text-color-placeholder);
}

.upload-tip {
  margin-top: 8px;
  font-size: 12px;
  color: var(--el-text-color-secondary);
}

.is-disabled .upload-trigger {
  cursor: not-allowed;
  opacity: 0.6;
}

.is-disabled .upload-trigger:hover {
  border-color: var(--el-border-color);
  background: var(--el-fill-color-lighter);
}
</style>
