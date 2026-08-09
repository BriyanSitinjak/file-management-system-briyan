<!--
  Soft-deleted folders and files with restore actions.
  Administrator only; uses SoftDeletes records from the trash API.
-->
<script setup>
import { onMounted, ref } from 'vue'
import { RotateCcw } from '@lucide/vue'
import api from '../lib/api'
import BaseButton from '../components/BaseButton.vue'
import DataTable from '../components/DataTable.vue'

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

// GET /trash for soft-deleted folders and files.
async function loadTrash() {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.get('/trash')
    folders.value = (data.folders || []).map((folder) => ({
      ...folder,
      department: folder.department?.name || '—',
      deleted_at: new Date(folder.deleted_at).toLocaleString(),
    }))
    files.value = (data.files || []).map((file) => ({
      ...file,
      folder: file.folder?.name || '—',
      deleted_at: new Date(file.deleted_at).toLocaleString(),
    }))
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load trash.'
  } finally {
    loading.value = false
  }
}

// POST /trash/folders/{id}/restore to undelete a folder.
async function restoreFolder(folder) {
  await api.post(`/trash/folders/${folder.id}/restore`)
  await loadTrash()
}

// POST /trash/files/{id}/restore to undelete a file.
async function restoreFile(file) {
  await api.post(`/trash/files/${file.id}/restore`)
  await loadTrash()
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

    <p v-if="loading" class="text-sm text-slate-500">Loading trash…</p>
    <p v-else-if="error" class="text-sm text-red-600">{{ error }}</p>

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
