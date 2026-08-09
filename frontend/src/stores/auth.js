import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token'))
  const user = ref(null)

  function setAuth(nextToken, nextUser = null) {
    token.value = nextToken
    user.value = nextUser

    if (nextToken) {
      localStorage.setItem('token', nextToken)
    } else {
      localStorage.removeItem('token')
    }
  }

  function clearAuth() {
    setAuth(null, null)
  }

  return {
    token,
    user,
    setAuth,
    clearAuth,
  }
})
