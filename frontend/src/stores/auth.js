import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import api from '../lib/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))

  const isAdmin = computed(() => user.value?.role === 'Administrator')

  // Writes token state and mirrors it to localStorage (set or remove).
  function persistToken(nextToken) {
    token.value = nextToken

    if (nextToken) {
      localStorage.setItem('token', nextToken)
    } else {
      localStorage.removeItem('token')
    }
  }

  // POST /login. Sets user and token from the response, then saves token to localStorage.
  async function login(email, password) {
    const { data } = await api.post('/login', { email, password })

    persistToken(data.token)
    user.value = data.user

    return data
  }

  // POST /logout when a token exists. Always clears user/token state and removes token from localStorage.
  async function logout() {
    try {
      if (token.value) {
        await api.post('/logout')
      }
    } catch {
      // Still clear local session if the API call fails.
    } finally {
      persistToken(null)
      user.value = null
    }
  }

  // GET /me. Updates user state from the API. Does not change token or localStorage.
  async function fetchMe() {
    const { data } = await api.get('/me')
    user.value = data.user

    return data.user
  }

  // Local-only reset used by 401 handling. Clears user/token state and removes token from localStorage.
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
