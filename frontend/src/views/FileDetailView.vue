<!--
  File detail screen with metadata and download action.
  Available to Administrator and Viewer; download is allowed for both roles.
-->
<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../lib/api'
import { useAuthStore } from '../stores/auth'
import BaseButton from '../components/BaseButton.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref('')
const file = ref(null)

// GET /files/{id} for the detail payload.
async function loadFile() {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.get(`/files/${route.params.id}`)
    file.value = data.file || data
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load file.'
  } finally {
    loading.value = false
  }
}

// GET /files/{id}/download and trigger a browser file save.
async function downloadFile() {
  const response = await api.get(`/files/${route.params.id}/download`, {
    responseType: 'blob',
  })

  const blobUrl = window.URL.createObjectURL(response.data)
  const link = document.createElement('a')
  link.href = blobUrl
  link.download = file.value?.original_name || file.value?.title || 'download'
  link.click()
  window.URL.revokeObjectURL(blobUrl)
}

// DELETE /files/{id} then return to the parent folder.
async function deleteFile() {
  if (!confirm(`Delete file "${file.value?.title}"?`)) return
  await api.delete(`/files/${route.params.id}`)
  await router.push(file.value?.folder_id ? `/folders/${file.value.folder_id}` : '/folders')
}

watch(
  () => route.params.id,
  () => loadFile(),
)

onMounted(loadFile)
</script>

<template>
  <section class="space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold">{{ file?.title || 'File detail' }}</h1>
        <p class="text-sm text-slate-600">Metadata and download for this file.</p>
      </div>

      <div class="flex flex-wrap gap-2">
        <BaseButton :disabled="!file" @click="downloadFile">Download</BaseButton>
        <!-- Admin only: destructive delete stays hidden for Viewers. -->
        <BaseButton
          v-if="auth.isAdmin"
          variant="danger"
          :disabled="!file"
          @click="deleteFile"
        >
          Delete
        </BaseButton>
      </div>
    </div>

    <p v-if="loading" class="text-sm text-slate-500">Loading file…</p>
    <p v-else-if="error" class="text-sm text-red-600">{{ error }}</p>

    <dl
      v-else-if="file"
      class="grid gap-4 rounded-lg border border-slate-200 bg-white p-4 sm:grid-cols-2"
    >
      <div>
        <dt class="text-sm text-slate-500">Folder name</dt>
        <dd class="font-medium">{{ file.folder?.name || '—' }}</dd>
      </div>
      <div>
        <dt class="text-sm text-slate-500">File name</dt>
        <dd class="font-medium">{{ file.original_name || '—' }}</dd>
      </div>
      <div>
        <dt class="text-sm text-slate-500">Title</dt>
        <dd class="font-medium">{{ file.title }}</dd>
      </div>
      <div>
        <dt class="text-sm text-slate-500">Department</dt>
        <dd class="font-medium">{{ file.department?.name || '—' }}</dd>
      </div>
      <div>
        <dt class="text-sm text-slate-500">Uploaded by</dt>
        <dd class="font-medium">{{ file.user?.name || '—' }}</dd>
      </div>
      <div>
        <dt class="text-sm text-slate-500">Upload date</dt>
        <dd class="font-medium">{{ new Date(file.created_at).toLocaleString() }}</dd>
      </div>
    </dl>
  </section>
</template>
