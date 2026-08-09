<script setup>
import { onMounted, ref } from 'vue'
import api from '../lib/api'
import { formatDateTime } from '../lib/dates'
import { getErrorMessage } from '../lib/errors'
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

async function loadLogs() {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.get('/activity-logs')
    logs.value = data.map((log) => ({
      ...log,
      user: log.user?.name || 'System',
      created_at: formatDateTime(log.created_at),
    }))
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load activity logs.')
  } finally {
    loading.value = false
  }
}

onMounted(loadLogs)
</script>

<template>
  <section class="space-y-5">
    <div>
      <p class="page-subtitle">
        Recent create, update, delete, and download events.
      </p>
    </div>

    <p v-if="loading" class="text-sm text-[var(--muted)]">Loading activity…</p>
    <p v-else-if="error" class="text-sm text-rose-600">{{ error }}</p>
    <DataTable
      v-else
      :columns="columns"
      :rows="logs"
      empty-text="No activity recorded yet."
    />
  </section>
</template>
