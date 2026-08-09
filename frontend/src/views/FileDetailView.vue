<!--
  File detail screen with metadata, download, and PDF/image preview.
  Available to Administrator and Viewer; delete stays admin only.
-->
<script setup>
import { computed, onMounted, onBeforeUnmount, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Download, Eye, Trash2 } from '@lucide/vue'
import api from '../lib/api'
import { useAuthStore } from '../stores/auth'
import BaseButton from '../components/BaseButton.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref('')
const file = ref(null)
const previewUrl = ref('')
const previewError = ref('')

const isImage = computed(() => (file.value?.mime_type || '').startsWith('image/'))
const isPdf = computed(() => file.value?.mime_type === 'application/pdf')
const canPreview = computed(() => isImage.value || isPdf.value)

// GET /files/{id} for the detail payload.
async function loadFile() {
  loading.value = true
  error.value = ''
  clearPreview()

  try {
    const { data } = await api.get(`/files/${route.params.id}`)
    file.value = data.file || data
    if (canPreview.value) {
      await loadPreview()
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load file.'
  } finally {
    loading.value = false
  }
}

// GET /files/{id}/preview as a blob and build an object URL for iframe/img.
async function loadPreview() {
  previewError.value = ''

  try {
    const response = await api.get(`/files/${route.params.id}/preview`, {
      responseType: 'blob',
    })
    previewUrl.value = window.URL.createObjectURL(response.data)
  } catch {
    previewError.value = 'Preview is unavailable for this file.'
  }
}

// Release the object URL when leaving the page or changing files.
function clearPreview() {
  if (previewUrl.value) {
    window.URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = ''
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
onBeforeUnmount(clearPreview)
</script>

<template>
  <section class="space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold tracking-tight">{{ file?.title || 'File detail' }}</h2>
        <p class="page-subtitle">Metadata, preview, and download.</p>
      </div>

      <div class="flex flex-wrap gap-2">
        <BaseButton :icon="Download" :disabled="!file" @click="downloadFile">
          Download
        </BaseButton>
        <!-- Admin only: destructive delete stays hidden for Viewers. -->
        <BaseButton
          v-if="auth.isAdmin"
          variant="danger"
          :icon="Trash2"
          :disabled="!file"
          @click="deleteFile"
        >
          Delete
        </BaseButton>
      </div>
    </div>

    <p v-if="loading" class="text-sm text-slate-500">Loading file…</p>
    <p v-else-if="error" class="text-sm text-red-600">{{ error }}</p>

    <template v-else-if="file">
      <dl class="surface-card grid gap-4 p-4 sm:grid-cols-2">
        <div>
          <dt class="text-sm text-[var(--muted)]">Folder name</dt>
          <dd class="font-semibold">{{ file.folder?.name || '—' }}</dd>
        </div>
        <div>
          <dt class="text-sm text-[var(--muted)]">File name</dt>
          <dd class="font-semibold">{{ file.original_name || '—' }}</dd>
        </div>
        <div>
          <dt class="text-sm text-[var(--muted)]">Title</dt>
          <dd class="font-semibold">{{ file.title }}</dd>
        </div>
        <div>
          <dt class="text-sm text-[var(--muted)]">Department</dt>
          <dd class="font-semibold">{{ file.department?.name || '—' }}</dd>
        </div>
        <div>
          <dt class="text-sm text-[var(--muted)]">Uploaded by</dt>
          <dd class="font-semibold">{{ file.user?.name || '—' }}</dd>
        </div>
        <div>
          <dt class="text-sm text-[var(--muted)]">Upload date</dt>
          <dd class="font-semibold">{{ new Date(file.created_at).toLocaleString() }}</dd>
        </div>
      </dl>

      <!-- Preview only for image and PDF mime types. -->
      <div
        v-if="canPreview"
        class="surface-card p-4"
      >
        <h2 class="mb-3 flex items-center gap-2 text-lg font-bold tracking-tight">
          <Eye class="size-5" :stroke-width="2" />
          Preview
        </h2>
        <p v-if="previewError" class="text-sm text-red-600">{{ previewError }}</p>
        <img
          v-else-if="isImage && previewUrl"
          :src="previewUrl"
          :alt="file.title"
          class="max-h-[32rem] w-full rounded object-contain"
        />
        <iframe
          v-else-if="isPdf && previewUrl"
          :src="previewUrl"
          class="h-[32rem] w-full rounded border border-slate-200 dark:border-slate-700"
          title="PDF preview"
        />
      </div>
    </template>
  </section>
</template>
