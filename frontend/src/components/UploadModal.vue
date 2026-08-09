<!--
  Admin only upload dialog for creating a file in the current folder.
  Viewers must not open this modal; parent screens gate the trigger button.
-->
<script setup>
import { ref, watch } from 'vue'
import { Upload, X } from '@lucide/vue'
import api from '../lib/api'
import BaseModal from './BaseModal.vue'
import BaseButton from './BaseButton.vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  folderId: {
    type: [Number, String, null],
    default: null,
  },
  departmentId: {
    type: [Number, String, null],
    default: null,
  },
})

const emit = defineEmits(['close', 'uploaded'])

const title = ref('')
const departmentId = ref('')
const file = ref(null)
const departments = ref([])
const error = ref('')
const loading = ref(false)
const dragging = ref(false)

// GET /departments for the department select options.
async function loadDepartments() {
  const { data } = await api.get('/departments')
  departments.value = data
}

// Keep the selected file from a standard file input change.
function onFileChange(event) {
  file.value = event.target.files?.[0] || null
}

// Highlight the dropzone while a file is dragged over it.
function onDragOver(event) {
  event.preventDefault()
  dragging.value = true
}

// Clear drag highlight when the pointer leaves the dropzone.
function onDragLeave() {
  dragging.value = false
}

// Accept a dropped file into the form state.
function onDrop(event) {
  event.preventDefault()
  dragging.value = false
  file.value = event.dataTransfer.files?.[0] || null
}

// POST /files with multipart form data for title, department, folder, and file.
async function submitUpload() {
  error.value = ''

  if (!title.value || !departmentId.value || !props.value.folderId || !file.value) {
    error.value = 'Title, department, folder, and file are required.'
    return
  }

  loading.value = true

  try {
    const formData = new FormData()
    formData.append('title', title.value)
    formData.append('department_id', departmentId.value)
    formData.append('folder_id', String(props.folderId))
    formData.append('file', file.value)

    await api.post('/files', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    title.value = ''
    file.value = null
    emit('uploaded')
    emit('close')
  } catch (err) {
    error.value = err.response?.data?.message || 'Upload failed.'
  } finally {
    loading.value = false
  }
}

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      departmentId.value = props.departmentId ? String(props.departmentId) : ''
      loadDepartments()
    }
  },
)
</script>

<template>
  <BaseModal :open="open" title="Upload file" @close="emit('close')">
    <form class="space-y-4" @submit.prevent="submitUpload">
      <label class="block space-y-1 text-sm">
        <span>Title</span>
        <input
          v-model="title"
          type="text"
          required
          class="w-full rounded border border-slate-300 px-3 py-2"
        />
      </label>

      <label class="block space-y-1 text-sm">
        <span>Department</span>
        <select
          v-model="departmentId"
          required
          class="w-full rounded border border-slate-300 px-3 py-2"
        >
          <option disabled value="">Select department</option>
          <option
            v-for="department in departments"
            :key="department.id"
            :value="String(department.id)"
          >
            {{ department.name }}
          </option>
        </select>
      </label>

      <div
        class="rounded border border-dashed px-4 py-8 text-center text-sm"
        :class="dragging ? 'border-slate-900 bg-slate-50' : 'border-slate-300'"
        @dragover="onDragOver"
        @dragleave="onDragLeave"
        @drop="onDrop"
      >
        <Upload class="mx-auto mb-2 size-8 text-slate-400" :stroke-width="1.75" />
        <p class="mb-2 text-slate-600">
          Drag and drop a file here, or choose one below.
        </p>
        <input type="file" @change="onFileChange" />
        <p v-if="file" class="mt-2 text-slate-800">Selected: {{ file.name }}</p>
      </div>

      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

      <div class="flex justify-end gap-2">
        <BaseButton variant="secondary" :icon="X" @click="emit('close')">Cancel</BaseButton>
        <BaseButton type="submit" :icon="Upload" :disabled="loading">
          {{ loading ? 'Uploading…' : 'Upload' }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
