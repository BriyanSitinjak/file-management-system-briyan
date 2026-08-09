import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import api from '../lib/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))

  const isAdmin = computed(() => user.value?.role === 'Administrator')

  function persistToken(nextToken) {
    token.value = nextToken

    if (nextToken) {
      localStorage.setItem('token', nextToken)
    } else {
      localStorage.removeItem('token')
    }
  }

  async function login(email, password) {
    const { data } = await api.post('/login', { email, password })

    persistToken(data.token)
    user.value = data.user

    return data
  }

  async function logout() {
    try {
      if (token.value) {
        await api.post('/logout')
      }
    } catch {
      // Clear local session even if the API call fails.
    } finally {
      persistToken(null)
      user.value = null
    }
  }

  async function fetchMe() {
    const { data } = await api.get('/me')
    user.value = data.user

    return data.user
  }

  function clearAuth() {
    persistToken(null)
    user.value = null
  }

  return {
    user,
    token,
    isAdmin,
    login,
    logout,
    fetchMe,
    clearAuth,
  }
})
