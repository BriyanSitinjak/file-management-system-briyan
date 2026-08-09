<!--
  Authenticated app chrome with sidebar navigation and top bar.
  Visible to Administrator and Viewer; admin-only links are hidden for Viewers.
-->
<script setup>
import { useRouter } from 'vue-router'
import {
  Activity,
  Building2,
  FolderOpen,
  LayoutDashboard,
  LogOut,
  Menu,
  Moon,
  Sun,
  Trash2,
} from '@lucide/vue'
import { AnimatePresence, motion } from 'motion-v'
import { useAuthStore } from '../stores/auth'
import { useUiStore } from '../stores/ui'
import BaseButton from '../components/BaseButton.vue'
import { slideDown, transitions } from '../lib/motion'

const auth = useAuthStore()
const ui = useUiStore()
const router = useRouter()

const navLinkClass =
  'flex items-center gap-2 rounded px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800'

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
        <p class="mb-6 flex items-center gap-2 text-sm font-semibold tracking-wide text-slate-500 dark:text-slate-400">
          <FolderOpen class="size-4" :stroke-width="2" />
          FMS
        </p>
        <nav class="space-y-2 text-sm">
          <RouterLink :class="navLinkClass" to="/">
            <LayoutDashboard class="size-4 shrink-0" :stroke-width="2" />
            Dashboard
          </RouterLink>
          <RouterLink :class="navLinkClass" to="/folders">
            <FolderOpen class="size-4 shrink-0" :stroke-width="2" />
            Folders
          </RouterLink>
          <!-- Admin only: department management stays hidden for Viewers. -->
          <RouterLink
            v-if="auth.isAdmin"
            :class="navLinkClass"
            to="/departments"
          >
            <Building2 class="size-4 shrink-0" :stroke-width="2" />
            Departments
          </RouterLink>
          <RouterLink
            v-if="auth.isAdmin"
            :class="navLinkClass"
            to="/activity"
          >
            <Activity class="size-4 shrink-0" :stroke-width="2" />
            Activity
          </RouterLink>
          <RouterLink
            v-if="auth.isAdmin"
            :class="navLinkClass"
            to="/trash"
          >
            <Trash2 class="size-4 shrink-0" :stroke-width="2" />
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
              class="inline-flex items-center gap-1 rounded border border-slate-300 px-2 py-1 text-sm md:hidden dark:border-slate-700"
              @click="ui.toggleMobileNav"
            >
              <Menu class="size-4" :stroke-width="2" />
              Menu
            </button>
            <div class="text-sm text-slate-600 dark:text-slate-300">
              {{ auth.user?.name }}
              <span class="text-slate-400">({{ auth.user?.role }})</span>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <BaseButton
              variant="secondary"
              :icon="ui.darkMode ? Sun : Moon"
              :aria-label="ui.darkMode ? 'Switch to light mode' : 'Switch to dark mode'"
              @click="ui.toggleDarkMode"
            />
            <BaseButton variant="secondary" :icon="LogOut" @click="onLogout">
              Logout
            </BaseButton>
          </div>
        </header>

        <!-- Mobile drawer -->
        <AnimatePresence>
          <motion.div
            v-if="ui.mobileNavOpen"
            key="mobile-nav"
            class="border-b border-slate-200 bg-white p-3 md:hidden dark:border-slate-800 dark:bg-slate-900"
            :initial="slideDown.initial"
            :animate="slideDown.animate"
            :exit="slideDown.exit"
            :transition="transitions.snappy"
          >
            <nav class="flex flex-col gap-2 text-sm">
              <button :class="navLinkClass + ' w-full text-left'" @click="go('/')">
                <LayoutDashboard class="size-4 shrink-0" :stroke-width="2" />
                Dashboard
              </button>
              <button :class="navLinkClass + ' w-full text-left'" @click="go('/folders')">
                <FolderOpen class="size-4 shrink-0" :stroke-width="2" />
                Folders
              </button>
              <button
                v-if="auth.isAdmin"
                :class="navLinkClass + ' w-full text-left'"
                @click="go('/departments')"
              >
                <Building2 class="size-4 shrink-0" :stroke-width="2" />
                Departments
              </button>
              <button
                v-if="auth.isAdmin"
                :class="navLinkClass + ' w-full text-left'"
                @click="go('/activity')"
              >
                <Activity class="size-4 shrink-0" :stroke-width="2" />
                Activity
              </button>
              <button
                v-if="auth.isAdmin"
                :class="navLinkClass + ' w-full text-left'"
                @click="go('/trash')"
              >
                <Trash2 class="size-4 shrink-0" :stroke-width="2" />
                Trash
              </button>
            </nav>
          </motion.div>
        </AnimatePresence>

        <main class="flex-1 p-4 md:p-6">
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>
