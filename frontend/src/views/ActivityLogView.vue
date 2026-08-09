<!--
  Admin activity feed listing recent mutations across folders and files.
  Administrator only; router and API both enforce the role check.
-->
<script setup>
import { onMounted, ref } from 'vue'
import api from '../lib/api'
import DataTable from '../components/DataTable.vue'

const loading = ref(true)
const error = ref('')
const logs = ref([])

const columns = [
  { key: 'created_at', label: 'When' },
  { key: 'user', label: 'User' },
  { key: 'action', label: 'Action' },
  { key: 'description', label: 'Details' },
]

// GET /activity-logs for the newest 100 mutation events.
async function loadLogs() {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.get('/activity-logs')
    logs.value = data.map((log) => ({
      ...log,
      user: log.user?.name || 'System',
      created_at: new Date(log.created_at).toLocaleString(),
    }))
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load activity logs.'
  } finally {
    loading.value = false
  }
}

onMounted(loadLogs)
</script>

<template>
  <section class="space-y-5">
    <div>
      <h1 class="text-2xl font-semibold dark:text-slate-100">Activity log</h1>
      <p class="text-sm text-slate-600 dark:text-slate-400">
        Recent create, update, delete, and download events.
      </p>
    </div>

    <p v-if="loading" class="text-sm text-slate-500">Loading activity…</p>
    <p v-else-if="error" class="text-sm text-red-600">{{ error }}</p>
    <DataTable
      v-else
      :columns="columns"
      :rows="logs"
      empty-text="No activity recorded yet."
    />
  </section>
</template>
