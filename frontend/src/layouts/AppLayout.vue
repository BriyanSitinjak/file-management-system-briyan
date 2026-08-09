<!--
  Authenticated app chrome with sidebar navigation and top bar.
  Visible to Administrator and Viewer; admin-only links are hidden for Viewers.
-->
<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import BaseButton from '../components/BaseButton.vue'

const auth = useAuthStore()
const router = useRouter()

// Call auth.logout which POSTs /logout, then return to the login screen.
async function onLogout() {
  await auth.logout()
  await router.replace('/login')
}
</script>

<template>
  <div class="min-h-screen bg-slate-100 text-slate-900">
    <div class="mx-auto flex min-h-screen max-w-6xl">
      <aside class="hidden w-56 shrink-0 border-r border-slate-200 bg-white p-4 md:block">
        <p class="mb-6 text-sm font-semibold tracking-wide text-slate-500">FMS</p>
        <nav class="space-y-2 text-sm">
          <RouterLink class="block rounded px-2 py-1.5 hover:bg-slate-50" to="/">
            Dashboard
          </RouterLink>
          <RouterLink class="block rounded px-2 py-1.5 hover:bg-slate-50" to="/folders">
            Folders
          </RouterLink>
          <!-- Admin only: department management stays hidden for Viewers. -->
          <RouterLink
            v-if="auth.isAdmin"
            class="block rounded px-2 py-1.5 hover:bg-slate-50"
            to="/departments"
          >
            Departments
          </RouterLink>
        </nav>
      </aside>

      <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3">
          <div class="text-sm text-slate-600">
            {{ auth.user?.name }}
            <span class="text-slate-400">({{ auth.user?.role }})</span>
          </div>
          <BaseButton variant="secondary" @click="onLogout">Logout</BaseButton>
        </header>

        <main class="flex-1 p-4 md:p-6">
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>
