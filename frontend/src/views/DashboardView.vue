<!--
  Dashboard overview with totals and the latest uploaded files.
  Available to Administrator and Viewer after authentication.
-->
<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { Building2, FileText, FolderOpen, Users } from '@lucide/vue'
import { motion } from 'motion-v'
import api from '../lib/api'
import DataTable from '../components/DataTable.vue'
import MotionFade from '../components/motion/MotionFade.vue'
import { fadeUp, staggerDelay, transitions } from '../lib/motion'

const loading = ref(true)
const error = ref('')
const totals = ref({
  folders: 0,
  files: 0,
  departments: 0,
  users: 0,
})
const latestFiles = ref([])

const columns = [
  { key: 'title', label: 'Title' },
  { key: 'department', label: 'Department' },
  { key: 'folder', label: 'Folder' },
  { key: 'user', label: 'Uploaded by' },
  { key: 'created_at', label: 'Uploaded' },
]

const cards = [
  { key: 'folders', label: 'Folders', icon: FolderOpen, tone: 'bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300' },
  { key: 'files', label: 'Files', icon: FileText, tone: 'bg-violet-50 text-violet-700 dark:bg-violet-950 dark:text-violet-300' },
  { key: 'departments', label: 'Departments', icon: Building2, tone: 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300' },
  { key: 'users', label: 'Users', icon: Users, tone: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' },
]

// GET /dashboard for totals and the 10 latest files.
async function loadDashboard() {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.get('/dashboard')
    totals.value = data.totals
    latestFiles.value = (data.latest_files || []).map((file) => ({
      ...file,
      department: file.department?.name || '—',
      folder: file.folder?.name || '—',
      user: file.user?.name || '—',
      created_at: new Date(file.created_at).toLocaleString(),
    }))
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load dashboard.'
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboard)
</script>

<template>
  <section class="space-y-6">
    <div>
      <h1 class="text-2xl font-semibold dark:text-slate-100">Dashboard</h1>
      <p class="text-sm text-slate-600 dark:text-slate-400">Overview of folders, files, and departments.</p>
    </div>

    <p v-if="loading" class="text-sm text-slate-500">Loading dashboard…</p>
    <p v-else-if="error" class="text-sm text-red-600">{{ error }}</p>

    <template v-else>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <motion.article
          v-for="(card, index) in cards"
          :key="card.key"
          class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900"
          :initial="fadeUp.initial"
          :animate="fadeUp.animate"
          :transition="{ ...transitions.soft, delay: staggerDelay(index) }"
          :while-hover="{ y: -2 }"
        >
          <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ card.label }}</p>
            <span class="inline-flex rounded-md p-2" :class="card.tone">
              <component :is="card.icon" class="size-4" :stroke-width="2" />
            </span>
          </div>
          <p class="mt-3 text-3xl font-semibold dark:text-slate-100">{{ totals[card.key] }}</p>
        </motion.article>
      </div>

      <MotionFade :delay="0.15" class="space-y-3">
        <h2 class="flex items-center gap-2 text-lg font-medium dark:text-slate-100">
          <FileText class="size-5" :stroke-width="2" />
          Latest files
        </h2>
        <DataTable :columns="columns" :rows="latestFiles" empty-text="No files uploaded yet.">
          <template #cell-title="{ row }">
            <RouterLink class="text-slate-900 underline dark:text-slate-100" :to="`/files/${row.id}`">
              {{ row.title }}
            </RouterLink>
          </template>
        </DataTable>
      </MotionFade>
    </template>
  </section>
</template>
