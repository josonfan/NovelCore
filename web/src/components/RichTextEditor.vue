<template>
  <div class="rte" :style="{ minHeight: height + 'px' }">
    <div class="rte-toolbar" v-if="!useQuill">
      <div class="tools-left">
        <el-tooltip content="加粗"><el-button class="icon-btn" text circle @click="cmd('bold')"><el-icon><component :is="icon('EditPen')" /></el-icon></el-button></el-tooltip>
        <el-tooltip content="斜体"><el-button class="icon-btn" text circle @click="cmd('italic')"><el-icon><component :is="icon('MagicStick')" /></el-icon></el-button></el-tooltip>
        <el-tooltip content="下划线"><el-button class="icon-btn" text circle @click="cmd('underline')"><el-icon><component :is="icon('Brush')" /></el-icon></el-button></el-tooltip>
        <el-tooltip content="无序列表"><el-button class="icon-btn" text circle @click="cmd('insertUnorderedList')"><el-icon><component :is="icon('List')" /></el-icon></el-button></el-tooltip>
        <el-tooltip content="有序列表"><el-button class="icon-btn" text circle @click="cmd('insertOrderedList')"><el-icon><component :is="icon('List')" /></el-icon></el-button></el-tooltip>
        <span class="divider"></span>
        <el-tooltip content="标题一"><el-button class="icon-btn" text circle @click="formatBlock('H1')">H1</el-button></el-tooltip>
        <el-tooltip content="标题二"><el-button class="icon-btn" text circle @click="formatBlock('H2')">H2</el-button></el-tooltip>
        <el-tooltip content="正文"><el-button class="icon-btn" text circle @click="formatBlock('P')">P</el-button></el-tooltip>
        <span class="divider"></span>
        <el-tooltip content="居左"><el-button class="icon-btn" text circle @click="cmd('justifyLeft')"><el-icon><component :is="icon('Menu')" /></el-icon></el-button></el-tooltip>
        <el-tooltip content="居中"><el-button class="icon-btn" text circle @click="cmd('justifyCenter')"><el-icon><component :is="icon('MoreFilled')" /></el-icon></el-button></el-tooltip>
        <el-tooltip content="居右"><el-button class="icon-btn" text circle @click="cmd('justifyRight')"><el-icon><component :is="icon('Operation')" /></el-icon></el-button></el-tooltip>
        <span class="divider"></span>
        <el-tooltip content="链接"><el-button class="icon-btn" text circle @click="insertLink()"><el-icon><component :is="icon('Link')" /></el-icon></el-button></el-tooltip>
        <el-tooltip content="清除样式"><el-button class="icon-btn" text circle @click="clear()"><el-icon><component :is="icon('Delete')" /></el-icon></el-button></el-tooltip>
      </div>
      <div class="tools-right">
        <span class="meta">字数：{{ plainLength }}</span>
      </div>
    </div>
    <div class="rte-editor" contenteditable="true" :placeholder="placeholder" ref="ed" v-html="content" @input="onInput" @blur="onInput" @paste="onPaste"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import * as Icons from '@element-plus/icons-vue'
const props = defineProps<{ modelValue: string; placeholder?: string; height?: number }>()
const emit = defineEmits<{ (e: 'update:modelValue', v: string): void }>()
const ed = ref<HTMLDivElement | null>(null)
const height = computed(() => Number(props.height ?? 280))
const content = ref(props.modelValue || '')
const useQuill = ref(false)
const QuillComp = ref<any>(null)
const toolbar = [
  [{ header: [1, 2, 3, false] }],
  ['bold', 'italic', 'underline'],
  [{ list: 'ordered' }, { list: 'bullet' }],
  [{ align: [] }],
  ['link', 'clean'],
]

onMounted(async () => {
  useQuill.value = false
  if (ed.value) ed.value.innerHTML = props.modelValue || ''
})

watch(() => props.modelValue, (v) => {
  content.value = v || ''
  if (!useQuill.value && ed.value && ed.value.innerHTML !== (v || '')) ed.value.innerHTML = v || ''
})
watch(content, (v) => emit('update:modelValue', v || ''))

function onInput() { content.value = (ed.value?.innerHTML || '').trim() }
function cmd(c: string) { document.execCommand(c, false) }
function clear() { document.execCommand('removeFormat', false) }
function onPaste(e: ClipboardEvent) { const d = e.clipboardData?.getData('text/plain') || ''; if (d) { e.preventDefault(); document.execCommand('insertText', false, d) } }
function formatBlock(tag: string) { document.execCommand('formatBlock', false, tag) }
function insertLink() { const url = prompt('输入链接地址'); if (url) document.execCommand('createLink', false, url) }
const plainLength = computed(() => (useQuill.value ? (content.value || '').replace(/<[^>]*>/g, '') : (ed.value?.innerText || '')).trim().length)
function icon(name: string){ return (Icons as any)[name] || (Icons as any).Brush }
</script>

<style scoped>
.rte { border: 1px solid var(--nc-border); border-radius: 10px; overflow: hidden; background: #fff; box-shadow: 0 8px 24px rgba(0,0,0,.04) }
.rte-toolbar { position: sticky; top: 0; display: flex; justify-content: space-between; align-items: center; gap: 6px; padding: 8px 10px; border-bottom: 1px solid var(--nc-border); background: rgba(248,250,252,.85); backdrop-filter: saturate(180%) blur(6px) }
.tools-left { display: flex; gap: 6px; flex-wrap: wrap }
.tools-left :deep(.el-button){ height: 28px; padding: 0 10px; border-radius: 14px }
.divider{ width: 1px; height: 18px; background: var(--nc-border); margin: 0 4px }
.tools-right { color: var(--nc-muted); font-size: 12px }
.rte-editor { min-height: 280px; padding: 12px; outline: none; line-height: 1.75; font-size: 14px }
.rte-editor:empty:before { content: attr(placeholder); color: #9ca3af }
</style>
