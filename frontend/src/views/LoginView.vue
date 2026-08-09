<!--
  Login screen for unauthenticated users.
  Guest only; authenticated users are redirected away by the router guard.
-->
<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { FolderOpen, LogIn } from '@lucide/vue'
import { useAuthStore } from '../stores/auth'
import BaseButton from '../components/BaseButton.vue'
import MotionFade from '../components/motion/MotionFade.vue'

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
  <div class="flex min-h-screen items-center justify-center bg-[var(--canvas)] px-4 py-10">
    <MotionFade class="w-full max-w-md">
      <p class="mb-3 text-sm font-bold tracking-tight text-[var(--brand)]">
        File management hub
      </p>
      <h1 class="page-title flex items-center gap-2">
        <FolderOpen class="size-8 text-[var(--brand)]" :stroke-width="2" />
        Welcome back
      </h1>
      <p class="page-subtitle mb-6">Sign in with a demo account to continue.</p>

      <form class="surface-card space-y-5 p-5" @submit.prevent="onSubmit">
        <label class="field-group">
          <span>Email</span>
          <input v-model="email" type="email" required class="field-input" />
        </label>

        <label class="field-group">
          <span>Password</span>
          <input v-model="password" type="password" required class="field-input" />
        </label>

        <p v-if="error" class="text-sm text-rose-600">{{ error }}</p>

        <BaseButton type="submit" :icon="LogIn" :disabled="loading" class="w-full">
          {{ loading ? 'Signing in…' : 'Sign in' }}
        </BaseButton>
      </form>
    </MotionFade>
  </div>
</template>
