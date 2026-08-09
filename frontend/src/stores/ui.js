import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

const emptyConfirm = () => ({
  open: false,
  title: '',
  message: '',
  confirmLabel: 'Delete',
  cancelLabel: 'Cancel',
  _resolve: null,
})

let toastSeq = 0

export const useUiStore = defineStore('ui', () => {
  const darkMode = ref(localStorage.getItem('darkMode') === 'true')
  const mobileNavOpen = ref(false)
  const toasts = ref([])
  const confirmDialog = ref(emptyConfirm())
  const toastTimers = new Map()

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

  function dismissToast(id) {
    const timer = toastTimers.get(id)
    if (timer) {
      clearTimeout(timer)
      toastTimers.delete(id)
    }
    toasts.value = toasts.value.filter((toast) => toast.id !== id)
  }

  // Push a toast; success auto-dismisses, errors stay a bit longer.
  function notify({ type = 'success', title, message = '', durationMs } = {}) {
    const id = ++toastSeq
    const duration = durationMs ?? (type === 'error' ? 4500 : 2800)

    toasts.value = [...toasts.value, { id, type, title, message }].slice(-4)

    if (duration > 0) {
      toastTimers.set(
        id,
        setTimeout(() => dismissToast(id), duration),
      )
    }
  }

  function notifySuccess(title, message = '') {
    notify({ type: 'success', title, message })
  }

  function notifyError(title, message = '') {
    notify({ type: 'error', title, message })
  }

  // Close the confirm modal; resolve the waiting promise with the result.
  function closeConfirm(result = false) {
    const resolve = confirmDialog.value._resolve
    confirmDialog.value = emptyConfirm()
    if (typeof resolve === 'function') {
      resolve(result)
    }
  }

  // Ask for confirmation (delete, etc.). Resolves true when confirmed.
  function confirm({
    title = 'Are you sure?',
    message = '',
    confirmLabel = 'Delete',
    cancelLabel = 'Cancel',
  } = {}) {
    if (typeof confirmDialog.value._resolve === 'function') {
      confirmDialog.value._resolve(false)
    }

    return new Promise((resolve) => {
      confirmDialog.value = {
        open: true,
        title,
        message,
        confirmLabel,
        cancelLabel,
        _resolve: resolve,
      }
    })
  }

  watch(darkMode, (enabled) => {
    document.documentElement.classList.toggle('dark', enabled)
  }, { immediate: true })

  return {
    darkMode,
    mobileNavOpen,
    toasts,
    confirmDialog,
    toggleDarkMode,
    toggleMobileNav,
    closeMobileNav,
    applyDarkMode,
    notify,
    notifySuccess,
    notifyError,
    dismissToast,
    confirm,
    closeConfirm,
  }
})
