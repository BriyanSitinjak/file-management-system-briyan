<!--
  Authenticated app chrome with sidebar navigation and top bar.
  Visible to Administrator and Viewer; admin-only links are hidden for Viewers.
-->
<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useUiStore } from '../stores/ui'
import BaseButton from '../components/BaseButton.vue'

const auth = useAuthStore()
const ui = useUiStore()
const router = useRouter()

// Call auth.logout which POSTs /logout, then return to the login screen.
async function onLogout() {
  await auth.logout()
  await router.replace('/login')
}

// Navigate from the mobile drawer and then close it.
async function go(path) {
  ui.closeMobileNav()
  await router.push(path)
}
</script>

<template>
  <div class="min-h-screen bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="mx-auto flex min-h-screen max-w-6xl">
      <!-- Desktop sidebar -->
      <aside class="hidden w-56 shrink-0 border-r border-slate-200 bg-white p-4 md:block dark:border-slate-800 dark:bg-slate-900">
        <p class="mb-6 text-sm font-semibold tracking-wide text-slate-500 dark:text-slate-400">FMS</p>
        <nav class="space-y-2 text-sm">
          <RouterLink class="block rounded px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800" to="/">
            Dashboard
          </RouterLink>
          <RouterLink class="block rounded px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800" to="/folders">
            Folders
          </RouterLink>
          <!-- Admin only: department management stays hidden for Viewers. -->
          <RouterLink
            v-if="auth.isAdmin"
            class="block rounded px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800"
            to="/departments"
          >
            Departments
          </RouterLink>
          <RouterLink
            v-if="auth.isAdmin"
            class="block rounded px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800"
            to="/activity"
          >
            Activity
          </RouterLink>
          <RouterLink
            v-if="auth.isAdmin"
            class="block rounded px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800"
            to="/trash"
          >
            Trash
          </RouterLink>
        </nav>
      </aside>

      <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3 dark:border-slate-800 dark:bg-slate-900">
          <div class="flex items-center gap-2">
            <!-- Mobile nav toggle: sidebar is hidden below the md breakpoint. -->
            <button
              type="button"
              class="rounded border border-slate-300 px-2 py-1 text-sm md:hidden dark:border-slate-700"
              @click="ui.toggleMobileNav"
            >
              Menu
            </button>
            <div class="text-sm text-slate-600 dark:text-slate-300">
              {{ auth.user?.name }}
              <span class="text-slate-400">({{ auth.user?.role }})</span>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <BaseButton variant="secondary" @click="ui.toggleDarkMode">
              {{ ui.darkMode ? 'Light' : 'Dark' }}
            </BaseButton>
            <BaseButton variant="secondary" @click="onLogout">Logout</BaseButton>
          </div>
        </header>

        <!-- Mobile drawer -->
        <div
          v-if="ui.mobileNavOpen"
          class="border-b border-slate-200 bg-white p-3 md:hidden dark:border-slate-800 dark:bg-slate-900"
        >
          <nav class="flex flex-col gap-2 text-sm">
            <button class="rounded px-2 py-1.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800" @click="go('/')">
              Dashboard
            </button>
            <button class="rounded px-2 py-1.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800" @click="go('/folders')">
              Folders
            </button>
            <button
              v-if="auth.isAdmin"
              class="rounded px-2 py-1.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800"
              @click="go('/departments')"
            >
              Departments
            </button>
            <button
              v-if="auth.isAdmin"
              class="rounded px-2 py-1.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800"
              @click="go('/activity')"
            >
              Activity
            </button>
            <button
              v-if="auth.isAdmin"
              class="rounded px-2 py-1.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800"
              @click="go('/trash')"
            >
              Trash
            </button>
          </nav>
        </div>

        <main class="flex-1 p-4 md:p-6">
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>
