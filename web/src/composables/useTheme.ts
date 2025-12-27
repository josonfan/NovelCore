import { ref, onMounted } from 'vue'

export interface ThemePalette {
  primary: string
  primaryDark: string
}

const PALETTES: ThemePalette[] = [
  { primary: '#3B82F6', primaryDark: '#2563EB' },
  { primary: '#409EFF', primaryDark: '#337ecc' },
  { primary: '#22C55E', primaryDark: '#16A34A' },
  { primary: '#F59E0B', primaryDark: '#D97706' },
  { primary: '#EF4444', primaryDark: '#DC2626' },
  { primary: '#8B5CF6', primaryDark: '#7C3AED' },
]

const STORAGE_KEY_PRIMARY = 'nc-primary'
const STORAGE_KEY_DARK = 'nc-primary600'

export function useTheme() {
  const activePrimary = ref('')

  function applyPalette(palette: ThemePalette) {
    activePrimary.value = palette.primary
    document.documentElement.style.setProperty('--nc-primary', palette.primary)
    document.documentElement.style.setProperty('--nc-primary-600', palette.primaryDark)
    try {
      localStorage.setItem(STORAGE_KEY_PRIMARY, palette.primary)
      localStorage.setItem(STORAGE_KEY_DARK, palette.primaryDark)
    } catch {
      // ignore storage errors
    }
  }

  function loadSavedTheme() {
    try {
      const primary = localStorage.getItem(STORAGE_KEY_PRIMARY)
      const dark = localStorage.getItem(STORAGE_KEY_DARK)
      if (primary && dark) {
        applyPalette({ primary, primaryDark: dark })
      }
    } catch {
      // ignore storage errors
    }
  }

  onMounted(loadSavedTheme)

  return {
    palettes: PALETTES,
    activePrimary,
    applyPalette,
  }
}
