import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useUiStore = defineStore('ui', () => {
  const darkMode = ref(localStorage.getItem('darkMode') === 'true')
  const mobileNavOpen = ref(false)

  // Apply the dark class on <html> and persist the preference.
  function applyDarkMode(enabled) {
    darkMode.value = enabled
    localStorage.setItem('darkMode', String(enabled))
    document.documentElement.classList.toggle('dark', enabled)
  }

  // Flip dark mode on or off from the top bar toggle.
  function toggleDarkMode() {
    applyDarkMode(!darkMode.value)
  }

  // Open or close the mobile sidebar drawer.
  function toggleMobileNav() {
    mobileNavOpen.value = !mobileNavOpen.value
  }

  function closeMobileNav() {
    mobileNavOpen.value = false
  }

  watch(darkMode, (enabled) => {
    document.documentElement.classList.toggle('dark', enabled)
  }, { immediate: true })

  return {
    darkMode,
    mobileNavOpen,
    toggleDarkMode,
    toggleMobileNav,
    closeMobileNav,
    applyDarkMode,
  }
})
