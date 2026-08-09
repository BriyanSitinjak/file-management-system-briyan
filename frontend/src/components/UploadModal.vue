<!--
  Admin only upload dialog for creating a file in the current folder.
  Viewers must not open this modal; parent screens gate the trigger button.
-->
<script setup>
import { computed, ref, watch } from 'vue'
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
    type: [Number, String],
    default: null,
  },
  departmentId: {
    type: [Number, String],
    default: null,
  },
})

const emit = defineEmits(['close', 'uploaded'])

const title = ref('')
const selectedDepartmentId = ref('')
const file = ref(null)
const departments = ref([])
const error = ref('')
const loading = ref(false)
const dragging = ref(false)
const fileInput = ref(null)

const resolvedFolderId = computed(() => {
  if (props.folderId === null || props.folderId === undefined || props.folderId === '') {
    return ''
  }
  return String(props.folderId)
})

// GET /departments for the department select options.
async function loadDepartments() {
  const { data } = await api.get('/departments')
  departments.value = data

  if (!selectedDepartmentId.value && props.departmentId) {
    selectedDepartmentId.value = String(props.departmentId)
  }
  if (!selectedDepartmentId.value && data[0]) {
    selectedDepartmentId.value = String(data[0].id)
  }
}

function resetForm() {
  title.value = ''
  file.value = null
  error.value = ''
  loading.value = false
  dragging.value = false
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

// Keep the selected file from a standard file input change.
function onFileChange(event) {
  file.value = event.target.files?.[0] || null
  error.value = ''
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
  error.value = ''
}

function openFilePicker() {
  fileInput.value?.click()
}

const MAX_FILE_BYTES = 20 * 1024 * 1024

function validate() {
  if (!title.value.trim()) return 'Title is required.'
  if (!selectedDepartmentId.value) return 'Department is required.'
  if (!resolvedFolderId.value) return 'Open a folder before uploading a file.'
  if (!file.value) return 'Choose a file to upload.'
  if (file.value.size > MAX_FILE_BYTES) return 'The file may not be greater than 20 MB.'
  return ''
}

// POST /files with multipart form data for title, department, folder, and file.
async function submitUpload() {
  error.value = validate()
  if (error.value) return

  loading.value = true

  try {
    const formData = new FormData()
    formData.append('title', title.value.trim())
    formData.append('department_id', selectedDepartmentId.value)
    formData.append('folder_id', resolvedFolderId.value)
    formData.append('file', file.value, file.value.name)

    // Let Axios set multipart + boundary automatically from FormData.
    await api.post('/files', formData)

    resetForm()
    emit('uploaded')
    emit('close')
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) {
      error.value = Object.values(errors).flat().join(' ')
    } else {
      error.value = err.response?.data?.message || 'Upload failed.'
    }
  } finally {
    loading.value = false
  }
}

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      error.value = ''
      selectedDepartmentId.value = props.departmentId ? String(props.departmentId) : ''
      loadDepartments()
    } else {
      resetForm()
    }
  },
)
</script>

<template>
  <BaseModal :open="open" title="Upload file" @close="emit('close')">
    <form class="space-y-5" novalidate @submit.prevent="submitUpload">
      <label class="field-group">
        <span>Title</span>
        <input
          v-model="title"
          type="text"
          required
          class="field-input"
        />
      </label>

      <label class="field-group">
        <span>Department</span>
        <select
          v-model="selectedDepartmentId"
          required
          class="field-input"
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
        class="rounded-xl border border-dashed px-4 py-8 text-center text-sm"
        :class="
          dragging
            ? 'border-[var(--brand)] bg-[var(--brand-soft)]'
            : 'border-[var(--line)] bg-[var(--canvas)]'
        "
        @dragover="onDragOver"
        @dragleave="onDragLeave"
        @drop="onDrop"
      >
        <Upload class="mx-auto mb-3 size-8 text-[var(--muted)]" :stroke-width="1.75" />
        <p class="mb-4 text-[var(--muted)]">
          Drag and drop a file here, or choose one below.
        </p>
        <input
          ref="fileInput"
          type="file"
          class="sr-only"
          @change="onFileChange"
        />
        <BaseButton type="button" variant="secondary" :icon="Upload" @click="openFilePicker">
          {{ file ? 'Change file' : 'Choose file' }}
        </BaseButton>
        <p v-if="file" class="mt-3 font-medium text-[var(--ink)]">Selected: {{ file.name }}</p>
      </div>

      <p v-if="error" class="text-sm text-rose-600">{{ error }}</p>

      <div class="flex justify-end gap-2">
        <BaseButton type="button" variant="secondary" :icon="X" @click="emit('close')">
          Cancel
        </BaseButton>
        <BaseButton
          type="button"
          :icon="Upload"
          :disabled="loading"
          @click="submitUpload"
        >
          {{ loading ? 'Uploading…' : 'Upload' }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
