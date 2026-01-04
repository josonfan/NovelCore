import { ref, watch, onUnmounted } from 'vue'
import { http } from '../api/http'
import { ElMessage } from 'element-plus'
import type { Site } from '../api/sites'

export interface HealthData {
  db: boolean
  cache: boolean
  lang: string
  ts: number
  queue?: Record<string, number | string>
}

export function useSiteHealth() {
  const health = ref<HealthData | null>(null)
  const healthLoading = ref(false)
  const healthFailed = ref(false)
  let healthTimer: ReturnType<typeof setInterval> | null = null

  function formatTime(ts: unknown) {
    const n = Number(ts || 0)
    if (!n) return ''
    const d = new Date(n * 1000)
    const y = d.getFullYear()
    const m = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    const hh = String(d.getHours()).padStart(2, '0')
    const mm = String(d.getMinutes()).padStart(2, '0')
    const ss = String(d.getSeconds()).padStart(2, '0')
    return `${y}-${m}-${day} ${hh}:${mm}:${ss}`
  }

  function stopHealthTimer() {
    if (healthTimer) {
      clearInterval(healthTimer)
      healthTimer = null
    }
  }

  async function checkHealth(site: Site | null, silent: boolean = false) {
    if (!site) return
    const base = String(site.base_api_url || '').replace(/\/$/, '')
    const token = String((site as { api_token?: string }).api_token || '')

    if (!base) {
      if (!silent) ElMessage.error('未配置基础API地址')
      healthFailed.value = true
      stopHealthTimer()
      return
    }
    if (!token) {
      if (!silent) ElMessage.error('未配置API令牌')
      healthFailed.value = true
      stopHealthTimer()
      return
    }

    try {
      healthLoading.value = !silent
      const res = await http.get(`${base}/Health/index`, {
        headers: { 'X-Api-Token': token },
      })
      const data = res?.data?.data || null
      health.value = data
      healthFailed.value = false
      if (!silent) ElMessage.success('检测完成')
    } catch (e: unknown) {
      healthFailed.value = true
      stopHealthTimer()
      if (!silent) {
        const err = e as { response?: { data?: { message?: string; msg?: string } } }
        const resp = err?.response?.data
        ElMessage.error(resp?.message || resp?.msg || '检测失败')
      }
    } finally {
      healthLoading.value = false
    }
  }

  function startAutoCheck(site: Site | null) {
    stopHealthTimer()
    if (!site) return
    checkHealth(site, true)
    healthTimer = setInterval(() => checkHealth(site, true), 5000)
  }

  function setupHealthWatch(showDetail: { value: boolean }, getSite: () => Site | null) {
    watch(
      () => showDetail.value,
      (v) => {
        if (v) {
          health.value = null
          healthFailed.value = false
          startAutoCheck(getSite())
        } else {
          stopHealthTimer()
        }
      }
    )

    onUnmounted(() => {
      stopHealthTimer()
    })
  }

  return {
    health,
    healthLoading,
    healthFailed,
    formatTime,
    checkHealth,
    stopHealthTimer,
    startAutoCheck,
    setupHealthWatch,
  }
}
