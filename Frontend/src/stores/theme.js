import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useThemeStore = defineStore('theme', () => {
  // 'dark' | 'light' | 'system'
  const currentPreference = ref(localStorage.getItem('future_self_theme') || 'dark')

  const systemTheme = ref(
    window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark'
  )

  // Listen for system theme changes
  if (typeof window !== 'undefined' && window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', (e) => {
      systemTheme.value = e.matches ? 'light' : 'dark'
      if (currentPreference.value === 'system') {
        applyTheme(systemTheme.value)
      }
    })
  }

  const activeTheme = computed(() => {
    if (currentPreference.value === 'system') {
      return systemTheme.value
    }
    return currentPreference.value
  })

  function applyTheme(theme) {
    if (typeof document === 'undefined') return
    if (theme === 'light') {
      document.documentElement.setAttribute('data-theme', 'light')
    } else {
      document.documentElement.removeAttribute('data-theme')
    }
  }

  function setTheme(theme) {
    currentPreference.value = theme
    localStorage.setItem('future_self_theme', theme)
    applyTheme(activeTheme.value)
  }

  function initTheme() {
    applyTheme(activeTheme.value)
  }

  return {
    currentPreference,
    activeTheme,
    setTheme,
    initTheme,
  }
})
