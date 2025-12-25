import { ref } from 'vue'
import { bindNovelTags, type Novel } from '../api/novels'
import { fetchTagOptions } from '../api/tags'
import { ElMessage } from 'element-plus'

export interface TagGroup {
  type: string
  label: string
  children: Array<{ id: number | string; name: string }>
}

export function useNovelTags() {
  const showTags = ref(false)
  const currentNovelId = ref<number | string>('')
  const tagGroups = ref<TagGroup[]>([])
  const tagsLoading = ref(false)
  const selectedTags = ref<Record<string, Array<number | string>>>({})

  async function openTags(row: Novel) {
    currentNovelId.value = row.id
    showTags.value = true
    tagsLoading.value = true
    tagGroups.value = []
    selectedTags.value = {}

    const types = ['theme', 'plot', 'role', 'r18', 'status', 'other']
    try {
      const groups = await Promise.all(
        types.map((t) => fetchTagOptions(t).catch(() => []))
      )
      const merged: TagGroup[] = []
      groups.forEach((arr: any) => {
        (arr || []).forEach((g: any) => merged.push(g))
      })
      tagGroups.value = merged

      const existing = Array.isArray(row.tags) ? row.tags : []
      const selectedByType: Record<string, Array<number | string>> = {}
      existing.forEach((t) => {
        const ty = String(t?.type || 'other')
        if (!selectedByType[ty]) selectedByType[ty] = []
        selectedByType[ty].push(t.id)
      })
      merged.forEach((g) => {
        const ty = String(g.type || 'other')
        selectedTags.value[ty] = selectedByType[ty] || []
      })
    } finally {
      tagsLoading.value = false
    }
  }

  async function submitTags() {
    try {
      const ids: Array<number | string> = Object.values(selectedTags.value).flat()
      const res = await bindNovelTags(currentNovelId.value, ids)
      if (res?.code === 200) {
        ElMessage.success('标签已设置')
        showTags.value = false
      } else {
        ElMessage.error(res?.msg || '设置失败')
      }
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string; msg?: string } } }
      const resp = err?.response?.data
      ElMessage.error(resp?.message || resp?.msg || '设置失败')
    }
  }

  function closeTags() {
    showTags.value = false
  }

  return {
    showTags,
    currentNovelId,
    tagGroups,
    tagsLoading,
    selectedTags,
    openTags,
    submitTags,
    closeTags,
  }
}
