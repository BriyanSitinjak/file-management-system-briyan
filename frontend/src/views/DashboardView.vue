<!--
  Dashboard overview with totals and the latest uploaded files.
  Available to Administrator and Viewer after authentication.
-->
<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../lib/api'
import DataTable from '../components/DataTable.vue'

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
      <h1 class="text-2xl font-semibold">Dashboard</h1>
      <p class="text-sm text-slate-600">Overview of folders, files, and departments.</p>
    </div>

    <p v-if="loading" class="text-sm text-slate-500">Loading dashboard…</p>
    <p v-else-if="error" class="text-sm text-red-600">{{ error }}</p>

    <template v-else>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-lg border border-slate-200 bg-white p-4">
          <p class="text-sm text-slate-500">Folders</p>
          <p class="mt-2 text-3xl font-semibold">{{ totals.folders }}</p>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-4">
          <p class="text-sm text-slate-500">Files</p>
          <p class="mt-2 text-3xl font-semibold">{{ totals.files }}</p>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-4">
          <p class="text-sm text-slate-500">Departments</p>
          <p class="mt-2 text-3xl font-semibold">{{ totals.departments }}</p>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-4">
          <p class="text-sm text-slate-500">Users</p>
          <p class="mt-2 text-3xl font-semibold">{{ totals.users }}</p>
        </article>
      </div>

      <div class="space-y-3">
        <h2 class="text-lg font-medium">Latest files</h2>
        <DataTable :columns="columns" :rows="latestFiles" empty-text="No files uploaded yet.">
          <template #cell-title="{ row }">
            <RouterLink class="text-slate-900 underline" :to="`/files/${row.id}`">
              {{ row.title }}
            </RouterLink>
          </template>
        </DataTable>
      </div>
    </template>
  </section>
</template>
