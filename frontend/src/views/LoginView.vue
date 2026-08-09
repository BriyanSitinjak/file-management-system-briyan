<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const email = ref('admin@example.com')
const password = ref('password')
const error = ref('')
const loading = ref(false)

async function onSubmit() {
  error.value = ''
  loading.value = true

  try {
    await auth.login(email.value, password.value)
    await router.replace(route.query.redirect || '/')
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4">
    <h1 class="mb-6 text-2xl font-semibold">Login</h1>

    <form class="space-y-4" @submit.prevent="onSubmit">
      <label class="block space-y-1">
        <span class="text-sm">Email</span>
        <input
          v-model="email"
          type="email"
          required
          class="w-full rounded border px-3 py-2"
        />
      </label>

      <label class="block space-y-1">
        <span class="text-sm">Password</span>
        <input
          v-model="password"
          type="password"
          required
          class="w-full rounded border px-3 py-2"
        />
      </label>

      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

      <button
        type="submit"
        class="w-full rounded bg-slate-900 px-3 py-2 text-white disabled:opacity-60"
        :disabled="loading"
      >
        {{ loading ? 'Signing in…' : 'Sign in' }}
      </button>
    </form>
  </div>
</template>
