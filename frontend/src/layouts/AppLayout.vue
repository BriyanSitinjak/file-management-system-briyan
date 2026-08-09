<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  Activity,
  Building2,
  FolderOpen,
  LayoutDashboard,
  LogOut,
  Menu,
  Moon,
  Search,
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
const route = useRoute()

const navItems = [
  { to: '/', label: 'Dashboard', icon: LayoutDashboard },
  { to: '/folders', label: 'Documents', icon: FolderOpen },
  { to: '/departments', label: 'Departments', icon: Building2, adminOnly: true },
  { to: '/activity', label: 'Activity', icon: Activity, adminOnly: true },
  { to: '/trash', label: 'Trash', icon: Trash2, adminOnly: true },
]

const initials = computed(() => {
  const name = auth.user?.name || 'User'
  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase())
    .join('')
})

const pageTitle = computed(() => {
  const titles = {
    dashboard: 'Dashboard',
    folders: 'Manage Documents',
    files: 'File Detail',
    departments: 'Departments',
    activity: 'Activity Log',
    trash: 'Trash',
  }
  return titles[route.name] || 'File Management'
})

async function onLogout() {
  const confirmed = await ui.confirm({
    title: 'Log out?',
    message: 'You will need to sign in again to continue.',
    confirmLabel: 'Logout',
  })
  if (!confirmed) return

  ui.closeMobileNav()
  await auth.logout()
  await router.replace('/login')
}

async function go(path) {
  ui.closeMobileNav()
  await router.push(path)
}

function onGlobalSearch(event) {
  const q = event.target.value?.trim()
  router.push({ name: 'folders', query: q ? { q } : {} })
}
</script>

<template>
  <div class="min-h-screen bg-[var(--canvas)] text-[var(--ink)]">
    <div class="mx-auto flex min-h-screen max-w-7xl">
      <aside class="hidden w-72 shrink-0 border-r border-[var(--line)] bg-[var(--panel)] p-5 md:flex md:flex-col">
        <div class="flex items-center justify-between gap-2">
          <p class="text-lg font-bold tracking-tight text-[var(--brand)]">
            File management hub
          </p>
          <BaseButton
            variant="ghost"
            :icon="ui.darkMode ? Sun : Moon"
            :aria-label="ui.darkMode ? 'Switch to light mode' : 'Switch to dark mode'"
            @click="ui.toggleDarkMode"
          />
        </div>

        <div class="surface-card mt-5 p-3">
          <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[var(--brand-soft)] text-sm font-bold text-[var(--brand-strong)]">
              {{ initials }}
            </div>
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold">{{ auth.user?.name }}</p>
              <p class="truncate text-xs text-[var(--muted)]">{{ auth.user?.role }}</p>
            </div>
          </div>
        </div>

        <nav class="mt-6 space-y-1">
          <template v-for="item in navItems" :key="item.to">
            <RouterLink
              v-if="item.adminOnly ? auth.isAdmin : true"
              class="nav-link"
              :to="item.to"
            >
              <component :is="item.icon" class="size-4 shrink-0" :stroke-width="2" />
              {{ item.label }}
            </RouterLink>
          </template>
        </nav>

        <div class="mt-auto space-y-2 pt-6">
          <BaseButton class="w-full" variant="secondary" :icon="LogOut" @click="onLogout">
            Logout
          </BaseButton>
        </div>
      </aside>

      <div class="flex min-w-0 flex-1 flex-col">
        <header class="border-b border-[var(--line)] bg-[var(--panel)] px-4 py-4 md:px-6">
          <div class="flex items-center gap-3">
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-xl border border-[var(--line)] px-2.5 py-2 text-sm md:hidden"
              @click="ui.toggleMobileNav"
            >
              <Menu class="size-4" :stroke-width="2" />
              Menu
            </button>

            <label class="relative min-w-0 flex-1">
              <Search class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-[var(--muted)]" :stroke-width="2" />
              <input
                class="field-input search-input"
                type="search"
                placeholder="Search documents..."
                :value="route.query.q || ''"
                @change="onGlobalSearch"
              />
            </label>

            <BaseButton
              class="md:hidden"
              variant="secondary"
              :icon="ui.darkMode ? Sun : Moon"
              :aria-label="ui.darkMode ? 'Switch to light mode' : 'Switch to dark mode'"
              @click="ui.toggleDarkMode"
            />
          </div>

          <div class="mt-5">
            <h1 class="page-title">{{ pageTitle }}</h1>
          </div>
        </header>

        <AnimatePresence>
          <motion.div
            v-if="ui.mobileNavOpen"
            key="mobile-nav"
            class="border-b border-[var(--line)] bg-[var(--panel)] p-3 md:hidden"
            :initial="slideDown.initial"
            :animate="slideDown.animate"
            :exit="slideDown.exit"
            :transition="transitions.snappy"
          >
            <nav class="flex flex-col gap-1">
              <template v-for="item in navItems" :key="item.to">
                <button
                  v-if="item.adminOnly ? auth.isAdmin : true"
                  class="nav-link w-full text-left"
                  @click="go(item.to)"
                >
                  <component :is="item.icon" class="size-4 shrink-0" :stroke-width="2" />
                  {{ item.label }}
                </button>
              </template>
              <button class="nav-link w-full text-left" @click="onLogout">
                <LogOut class="size-4 shrink-0" :stroke-width="2" />
                Logout
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
