<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

async function onLogout() {
  await auth.logout()
  await router.replace('/login')
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <header class="border-b bg-white">
      <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-3">
        <nav class="flex flex-wrap items-center gap-4 text-sm">
          <RouterLink to="/">Dashboard</RouterLink>
          <RouterLink to="/folders">Folders</RouterLink>
          <RouterLink v-if="auth.isAdmin" to="/departments">Departments</RouterLink>
        </nav>

        <div class="flex items-center gap-3 text-sm">
          <span>{{ auth.user?.email }}</span>
          <button
            type="button"
            class="rounded border px-2 py-1"
            @click="onLogout"
          >
            Logout
          </button>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-6">
      <slot />
    </main>
  </div>
</template>
