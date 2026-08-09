<script setup>
import { onMounted, ref } from 'vue'
import { RotateCcw } from '@lucide/vue'
import api from '../lib/api'
import { formatDateTime } from '../lib/dates'
import { getErrorMessage } from '../lib/errors'
import { useUiStore } from '../stores/ui'
import BaseButton from '../components/BaseButton.vue'
import DataTable from '../components/DataTable.vue'

const ui = useUiStore()
const loading = ref(true)
const error = ref('')
const folders = ref([])
const files = ref([])

const folderColumns = [
  { key: 'name', label: 'Folder' },
  { key: 'department', label: 'Department' },
  { key: 'deleted_at', label: 'Deleted' },
  { key: 'actions', label: 'Actions' },
]

const fileColumns = [
  { key: 'title', label: 'File' },
  { key: 'folder', label: 'Folder' },
  { key: 'deleted_at', label: 'Deleted' },
  { key: 'actions', label: 'Actions' },
]

async function loadTrash() {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.get('/trash')
    folders.value = (data.folders || []).map((folder) => ({
      ...folder,
      department: folder.department?.name || '—',
      deleted_at: formatDateTime(folder.deleted_at),
    }))
    files.value = (data.files || []).map((file) => ({
      ...file,
      folder: file.folder?.name || '—',
      deleted_at: formatDateTime(file.deleted_at),
    }))
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load trash.')
  } finally {
    loading.value = false
  }
}

async function restoreFolder(folder) {
  try {
    await api.post(`/trash/folders/${folder.id}/restore`)
    await loadTrash()
    ui.notifySuccess('Folder restored', `"${folder.name}" is available again.`)
  } catch (err) {
    ui.notifyError('Could not restore folder', getErrorMessage(err, 'Please try again.'))
  }
}

async function restoreFile(file) {
  try {
    await api.post(`/trash/files/${file.id}/restore`)
    await loadTrash()
    ui.notifySuccess('File restored', `"${file.title}" is available again.`)
  } catch (err) {
    ui.notifyError('Could not restore file', getErrorMessage(err, 'Please try again.'))
  }
}

onMounted(loadTrash)
</script>

<template>
  <section class="space-y-6">
    <div>
      <p class="page-subtitle">
        Soft-deleted items that can still be restored.
      </p>
    </div>

    <p v-if="loading" class="text-sm text-[var(--muted)]">Loading trash…</p>
    <p v-else-if="error" class="text-sm text-rose-600">{{ error }}</p>

    <template v-else>
      <div class="space-y-3">
        <h2 class="text-lg font-medium dark:text-slate-100">Folders</h2>
        <DataTable :columns="folderColumns" :rows="folders" empty-text="No deleted folders.">
          <template #cell-actions="{ row }">
            <BaseButton variant="secondary" :icon="RotateCcw" @click="restoreFolder(row)">
              Restore
            </BaseButton>
          </template>
        </DataTable>
      </div>

      <div class="space-y-3">
        <h2 class="text-lg font-medium dark:text-slate-100">Files</h2>
        <DataTable :columns="fileColumns" :rows="files" empty-text="No deleted files.">
          <template #cell-actions="{ row }">
            <BaseButton variant="secondary" :icon="RotateCcw" @click="restoreFile(row)">
              Restore
            </BaseButton>
          </template>
        </DataTable>
      </div>
    </template>
  </section>
</template>
