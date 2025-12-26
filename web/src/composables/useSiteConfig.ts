import { ref } from 'vue'
import { ElMessage } from 'element-plus'

export interface ConfigOptions<T> {
  fetchFn: (siteId: number) => Promise<T | null>
  saveFn: (data: T & { site_id: number }) => Promise<{ code?: number; msg?: string }>
  defaultValue: () => T
}

export function useSiteConfig<T extends object>(
  siteId: number,
  options: ConfigOptions<T>
) {
  const config = ref<T>(options.defaultValue())
  const loading = ref(false)
  const saving = ref(false)

  async function load() {
    try {
      loading.value = true
      const data = await options.fetchFn(siteId)
      config.value = data || options.defaultValue()
    } catch {
      config.value = options.defaultValue()
    } finally {
      loading.value = false
    }
  }

  async function save() {
    try {
      saving.value = true
      const payload = { site_id: siteId, ...config.value } as T & { site_id: number }
      const res = await options.saveFn(payload)
      if (res?.code === 200) {
        ElMessage.success('保存成功')
      } else {
        ElMessage.error(res?.msg || '保存失败')
      }
    } catch (e: unknown) {
      const resp = (e as { response?: { data?: { message?: string; msg?: string } } })?.response?.data
      ElMessage.error(resp?.message || resp?.msg || '保存失败')
    } finally {
      saving.value = false
    }
  }

  return {
    config,
    loading,
    saving,
    load,
    save,
  }
}

// 通用字段标签映射
export const fieldLabelMap: Record<string, string> = {
  provider: '服务商',
  base_url: '基础地址',
  index_name: '索引名称',
  api_key: '访问密钥',
  model: '模型',
  webhook_url: '回调地址',
  bot_token: 'Bot Token',
  chat_id: 'Chat ID',
  is_active: '启用',
  region: '区域',
  access_key: '访问密钥',
  secret_key: '密钥',
  from_address: '发件邮箱',
  from_name: '发件人名称',
  access_key_id: '访问密钥ID',
  bucket_name: '存储桶名称',
  bucket_region: '存储区域',
  endpoint: '接口地址',
  allowed_suffix: '允许后缀',
}

export function getFieldLabel(key: string): string {
  return fieldLabelMap[key] || key
}

// 过滤隐藏字段
export function filterConfigKeys(obj: Record<string, unknown>): string[] {
  const hidden = ['id', 'site_id', 'updated_at', 'created_at']
  return Object.keys(obj).filter((k) => !hidden.includes(k))
}
