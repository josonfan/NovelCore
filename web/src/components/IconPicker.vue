<template>
  <div class="picker">
    <el-input v-model="q" placeholder="搜索图标" clearable class="search" />
    <el-segmented v-model="cat" :options="cats" class="seg" />
    <div class="grid">
      <el-tooltip v-for="it in filtered" :key="it.value" :content="cnLabel(it)" placement="top">
        <div class="item" :class="{ active: it.value===model }" @click="choose(it.value)">
          <el-icon :size="22"><component :is="it.comp" /></el-icon>
        </div>
      </el-tooltip>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import * as Icons from '@element-plus/icons-vue'

const props = defineProps<{ modelValue: string }>()
const emit = defineEmits(['update:modelValue'])
const model = ref(props.modelValue || '')
const q = ref('')

type Item = { label: string; value: string; name: string; comp: any; tags: string[] }
const make = (label: string, name: string, tags: string[] = []) => ({ label, name, value: `el-icon-${name.replace(/[A-Z]/g, s=>'-'+s.toLowerCase()).replace(/^-/, '')}`, comp: (Icons as any)[name], tags }) as Item
const base: Item[] = [
  make('首页', 'House', ['导航']),
  make('系统', 'Setting', ['系统']),
  make('菜单', 'Menu', ['导航']),
  make('用户', 'User', ['用户']),
  make('角色', 'UserFilled', ['用户','权限']),
  make('头像', 'Avatar', ['用户']),
  make('钥匙', 'Key', ['权限','系统']),
  make('锁定', 'Lock', ['权限','系统']),
  make('解锁', 'Unlock', ['权限','系统']),
  make('文档', 'Document', ['内容']),
  make('编辑', 'Edit', ['内容']),
  make('列表', 'List', ['内容']),
  make('文件夹', 'Folder', ['内容']),
  make('新增', 'Plus', ['操作']),
  make('删除', 'Delete', ['操作','危险']),
  make('成功', 'CircleCheck', ['状态','成功']),
  make('警告', 'Warning', ['状态','警告']),
  make('信息', 'InfoFilled', ['状态','信息']),
  make('星标', 'Star', ['收藏']),
  make('看板', 'DataBoard', ['仪表盘']),
  make('统计', 'Histogram', ['仪表盘']),
  make('搜索', 'Search', ['导航']),
  make('通知', 'Bell', ['状态']),
  make('刷新', 'Refresh', ['操作']),
  make('上传', 'Upload', ['操作']),
  make('下载', 'Download', ['操作']),
  make('图片', 'Picture', ['内容']),
  make('链接', 'Link', ['内容']),
  make('位置', 'Location', ['导航']),
  make('日历', 'Calendar', ['时间']),
  make('时间', 'Clock', ['时间']),
  make('票据', 'Tickets', ['内容']),
  make('消息', 'Message', ['状态']),
  make('收集', 'Collection', ['内容']),
  make('标签', 'CollectionTag', ['内容','运营']),
  make('切换', 'SwitchButton', ['操作']),
  make('趋势', 'TrendCharts', ['仪表盘']),
  make('监控', 'Monitor', ['仪表盘']),
  make('推广', 'Promotion', ['运营']),
  make('CPU', 'Cpu', ['系统']),
  make('画笔', 'Brush', ['内容']),
  make('学校', 'School', ['内容']),
  make('相机', 'Camera', ['内容']),
  make('视频', 'VideoCamera', ['内容']),
]
function tagsByName(name: string): string[] {
  const n = name.toLowerCase()
  const tags: string[] = []
  if (/house|menu|location|compass|map|guide/.test(n)) tags.push('导航')
  if (/setting|gear|tools|cpu|config|system/.test(n)) tags.push('系统')
  if (/user|avatar|account|profile/.test(n)) tags.push('用户')
  if (/key|lock|unlock|shield|security|safety/.test(n)) tags.push('权限')
  if (/document|edit|folder|file|picture|camera|video|tickets|school|brush|collection(tag)?/.test(n)) tags.push('内容')
  if (/plus|add|delete|remove|upload|download|refresh|switch|link/.test(n)) tags.push('操作')
  if (/warning|info|circlecheck|success|message|bell|alert|error/.test(n)) tags.push('状态')
  if (/databoard|histogram|trendcharts|monitor|chart|dashboard/.test(n)) tags.push('仪表盘')
  if (/calendar|clock|time|timer|date/.test(n)) tags.push('时间')
  if (/promotion|marketing|collectiontag|tag/.test(n)) tags.push('运营')
  if (!tags.length) tags.push('全部')
  return tags
}
const autoAll = Object.keys(Icons).map((name: string) => make('', name, tagsByName(name)))
const list = (() => {
  const map = new Map<string, Item>()
  for (const it of [...base, ...autoAll]) {
    if (it.comp) map.set(it.value, it)
  }
  return Array.from(map.values())
})()
const cats = ['全部','导航','系统','用户','权限','内容','操作','状态','仪表盘','时间','运营']
const cat = ref('全部')

const filtered = computed(() => {
  const k = q.value.trim().toLowerCase()
  const pool = cat.value === '全部' ? list : list.filter(it => it.tags.includes(cat.value))
  if (!k) return pool
  return pool.filter((it) => it.value.toLowerCase().includes(k))
})

function choose(val: string) {
  model.value = val
  emit('update:modelValue', val)
}

function cnLabel(it: Item): string {
  if (it.label) return it.label
  const n = it.name.toLowerCase()
  const m: Record<string, string> = {
    house: '首页', setting: '系统', menu: '菜单', user: '用户', userfilled: '角色', avatar: '头像',
    key: '钥匙', lock: '锁定', unlock: '解锁', document: '文档', edit: '编辑', list: '列表', folder: '文件夹',
    plus: '新增', delete: '删除', circlecheck: '成功', warning: '警告', infofilled: '信息', star: '星标',
    databoard: '看板', histogram: '统计', search: '搜索', bell: '通知', refresh: '刷新', upload: '上传',
    download: '下载', picture: '图片', link: '链接', location: '位置', calendar: '日历', clock: '时间',
    tickets: '票据', message: '消息', collection: '收集', collectiontag: '标签', switchbutton: '切换',
    trendcharts: '趋势', monitor: '监控', promotion: '推广', cpu: 'CPU', brush: '画笔', school: '学校',
    camera: '相机', videocamera: '视频'
  }
  return m[n] || it.value
}
</script>

<style scoped>
.picker { display: grid; gap: 8px; width: 100%; box-sizing: border-box; }
.search { }
.seg { overflow-x: auto; }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(44px, 1fr)); gap: 8px; max-height: 320px; overflow-y: auto; overflow-x: hidden; }
.item { display: flex; align-items: center; justify-content: center; padding: 8px; border: 1px solid var(--nc-border); border-radius: 8px; cursor: pointer; box-sizing: border-box; }
.item:hover { background: rgba(64,158,255,0.08); }
.item.active { border-color: var(--nc-primary); background: rgba(64,158,255,0.08); }
.label { display:none; }
:deep(.el-segmented__group){ flex-wrap: nowrap; overflow-x: auto; gap: 6px; }
:deep(.el-segmented__item-label){ white-space: nowrap; }
</style>
