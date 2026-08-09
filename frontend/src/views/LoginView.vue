<!--
  Login screen for unauthenticated users.
  Guest only; authenticated users are redirected away by the router guard.
-->
<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import BaseButton from '../components/BaseButton.vue'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const email = ref('admin@example.com')
const password = ref('password')
const error = ref('')
const loading = ref(false)

// Call auth.login which POSTs /login, then redirect to the intended page.
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
    <h1 class="mb-2 text-2xl font-semibold text-slate-900">File Management</h1>
    <p class="mb-6 text-sm text-slate-600">Sign in with a demo account to continue.</p>

    <form class="space-y-4 rounded-lg border border-slate-200 bg-white p-5" @submit.prevent="onSubmit">
      <label class="block space-y-1 text-sm">
        <span>Email</span>
        <input
          v-model="email"
          type="email"
          required
          class="w-full rounded border border-slate-300 px-3 py-2"
        />
      </label>

      <label class="block space-y-1 text-sm">
        <span>Password</span>
        <input
          v-model="password"
          type="password"
          required
          class="w-full rounded border border-slate-300 px-3 py-2"
        />
      </label>

      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

      <BaseButton type="submit" :disabled="loading" class="w-full">
        {{ loading ? 'Signing in…' : 'Sign in' }}
      </BaseButton>
    </form>
  </div>
</template>
