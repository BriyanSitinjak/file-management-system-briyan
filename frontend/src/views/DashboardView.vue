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
  { key: 'folders', label: 'Folders', icon: FolderOpen, tone: 'bg-sky-100 text-sky-700' },
  { key: 'files', label: 'Files', icon: FileText, tone: 'bg-[var(--brand-soft)] text-[var(--brand-strong)]' },
  { key: 'departments', label: 'Departments', icon: Building2, tone: 'bg-amber-100 text-amber-700' },
  { key: 'users', label: 'Users', icon: Users, tone: 'bg-lime-100 text-lime-700' },
]

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
      <p class="page-subtitle">Overview of folders, files, and departments.</p>
    </div>

    <p v-if="loading" class="text-sm text-[var(--muted)]">Loading dashboard…</p>
    <p v-else-if="error" class="text-sm text-rose-600">{{ error }}</p>

    <template v-else>
      <div class="grid grid-cols-1 items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <motion.article
          v-for="(card, index) in cards"
          :key="card.key"
          class="surface-card flex h-full min-h-[8rem] w-full flex-col justify-between p-5"
          :initial="fadeUp.initial"
          :animate="fadeUp.animate"
          :transition="{ ...transitions.soft, delay: staggerDelay(index) }"
          :while-hover="{ y: -2 }"
        >
          <div class="flex items-start justify-between gap-3">
            <p class="pt-1 text-sm font-medium leading-none text-[var(--muted)]">
              {{ card.label }}
            </p>
            <span
              class="inline-flex size-10 shrink-0 items-center justify-center rounded-full"
              :class="card.tone"
            >
              <component :is="card.icon" class="size-4" :stroke-width="2" />
            </span>
          </div>
          <p class="text-3xl font-bold leading-none tracking-tight tabular-nums">
            {{ totals[card.key] }}
          </p>
        </motion.article>
      </div>

      <MotionFade :delay="0.15" class="space-y-4">
        <h2 class="text-lg font-bold tracking-tight">Latest files</h2>
        <DataTable :columns="columns" :rows="latestFiles" empty-text="No files uploaded yet.">
          <template #cell-title="{ row }">
            <RouterLink class="font-semibold text-[var(--brand-strong)] hover:underline" :to="`/files/${row.id}`">
              {{ row.title }}
            </RouterLink>
          </template>
        </DataTable>
      </MotionFade>
    </template>
  </section>
</template>
