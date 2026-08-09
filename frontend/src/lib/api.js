import axios from 'axios'
import { useAuthStore } from '../stores/auth'

// Shared Axios client for all API calls. baseURL points at the Laravel API
// so callers can use short paths like '/login' instead of the full host.
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
})

// Attach the Sanctum token on every request so protected routes authenticate
// without each caller setting Authorization by hand.
api.interceptors.request.use((config) => {
  const authStore = useAuthStore()

  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`
  }

  return config
})

// On 401, clear the stored session and send the user to login. That keeps the
// UI from calling protected endpoints with an expired or revoked token.
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Skip login itself: invalid credentials are also 401 and should stay on the form.
    if (error.response?.status === 401 && !error.config?.url?.includes('/login')) {
      const authStore = useAuthStore()
      authStore.clearAuth()

      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }

    return Promise.reject(error)
  },
)

export default api
