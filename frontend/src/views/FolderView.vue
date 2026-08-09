<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import {
  Check,
  FolderPlus,
  Upload,
  X,
} from '@lucide/vue'
import api from '../lib/api'
import { getErrorMessage } from '../lib/errors'
import { useDepartments } from '../composables/useDepartments'
import { useAuthStore } from '../stores/auth'
import { useUiStore } from '../stores/ui'
import BaseButton from '../components/BaseButton.vue'
import BaseModal from '../components/BaseModal.vue'
import BreadcrumbNav from '../components/BreadcrumbNav.vue'
import FolderCard from '../components/FolderCard.vue'
import SearchFilterBar from '../components/SearchFilterBar.vue'
import UploadModal from '../components/UploadModal.vue'

const auth = useAuthStore()
const ui = useUiStore()
const route = useRoute()
const { departments, loadDepartments } = useDepartments()

const loading = ref(false)
const error = ref('')
const folder = ref(null)
const breadcrumbs = ref([])
const childFolders = ref([])
const files = ref([])
const filters = ref({ q: '', department_id: '' })

const showCreateFolder = ref(false)
const showRenameFolder = ref(false)
const showUpload = ref(false)
const folderName = ref('')
const renameTarget = ref(null)
const createDepartmentId = ref('')

const folderId = computed(() => route.params.id || null)
const isRoot = computed(() => !folderId.value)

async function ensureDepartments() {
  await loadDepartments()
  if (!createDepartmentId.value && departments.value[0]) {
    createDepartmentId.value = String(departments.value[0].id)
  }
}

async function loadFolderContents() {
  loading.value = true
  error.value = ''

  try {
    const params = {
      q: filters.value.q || undefined,
      department_id: filters.value.department_id || undefined,
    }

    if (isRoot.value) {
      const { data } = await api.get('/folders', {
        params: { ...params, parent_id: '' },
      })
      folder.value = null
      breadcrumbs.value = [{ id: null, name: 'Root' }]
      childFolders.value = data
      files.value = []
    } else {
      const { data } = await api.get(`/folders/${folderId.value}`)
      folder.value = data.folder
      breadcrumbs.value = data.breadcrumbs || []
      childFolders.value = data.folder.children || []
      files.value = mapFiles(data.folder.files || [])

      if (filters.value.q) {
        const q = filters.value.q.toLowerCase()
        childFolders.value = childFolders.value.filter((item) =>
          item.name.toLowerCase().includes(q),
        )
        files.value = files.value.filter((item) =>
          item.title.toLowerCase().includes(q),
        )
      }
      if (filters.value.department_id) {
        const departmentId = Number(filters.value.department_id)
        childFolders.value = childFolders.value.filter(
          (item) => item.department_id === departmentId,
        )
        files.value = files.value.filter((item) => item.department_id === departmentId)
      }
    }
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load folders.')
  } finally {
    loading.value = false
  }
}

function mapFiles(items) {
  return items.map((file) => ({
    ...file,
    department: file.department?.name || '—',
    user: file.user?.name || '—',
  }))
}

function onSearch(nextFilters) {
  filters.value = nextFilters
  loadFolderContents()
}

async function createFolder() {
  if (!folderName.value.trim()) return

  try {
    await api.post('/folders', {
      name: folderName.value.trim(),
      department_id: Number(createDepartmentId.value || folder.value?.department_id),
      parent_id: folderId.value ? Number(folderId.value) : null,
    })

    folderName.value = ''
    showCreateFolder.value = false
    await loadFolderContents()
    ui.notifySuccess('Folder created', 'The new folder is ready to use.')
  } catch (err) {
    ui.notifyError('Could not create folder', getErrorMessage(err, 'Please try again.'))
  }
}

function openRename(target) {
  renameTarget.value = target
  folderName.value = target.name
  showRenameFolder.value = true
}

async function renameFolder() {
  if (!renameTarget.value || !folderName.value.trim()) return

  try {
    await api.put(`/folders/${renameTarget.value.id}`, {
      name: folderName.value.trim(),
    })

    showRenameFolder.value = false
    renameTarget.value = null
    folderName.value = ''
    await loadFolderContents()
    ui.notifySuccess('Folder updated', 'The folder name was saved.')
  } catch (err) {
    ui.notifyError('Could not update folder', getErrorMessage(err, 'Please try again.'))
  }
}

async function deleteFolder(target) {
  const confirmed = await ui.confirm({
    title: 'Delete folder?',
    message: `"${target.name}" will be moved to trash.`,
    confirmLabel: 'Delete',
  })
  if (!confirmed) return

  try {
    await api.delete(`/folders/${target.id}`)
    await loadFolderContents()
    ui.notifySuccess('Folder deleted', `"${target.name}" was moved to trash.`)
  } catch (err) {
    ui.notifyError('Could not delete folder', getErrorMessage(err, 'Please try again.'))
  }
}

async function deleteFile(target) {
  const confirmed = await ui.confirm({
    title: 'Delete file?',
    message: `"${target.title}" will be moved to trash.`,
    confirmLabel: 'Delete',
  })
  if (!confirmed) return

  try {
    await api.delete(`/files/${target.id}`)
    await loadFolderContents()
    ui.notifySuccess('File deleted', `"${target.title}" was moved to trash.`)
  } catch (err) {
    ui.notifyError('Could not delete file', getErrorMessage(err, 'Please try again.'))
  }
}

function onUploaded() {
  loadFolderContents()
  ui.notifySuccess('File uploaded', 'The file was saved successfully.')
}

watch(
  () => [route.params.id, route.query.q],
  () => {
    if (typeof route.query.q === 'string') {
      filters.value.q = route.query.q
    }
    loadFolderContents()
  },
  { immediate: true },
)

ensureDepartments()
</script>

<template>
  <section class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <BreadcrumbNav :items="breadcrumbs" />

      <div class="flex flex-wrap items-center gap-2">
        <template v-if="auth.isAdmin">
          <BaseButton :icon="FolderPlus" @click="showCreateFolder = true">
            Create New Folder
          </BaseButton>
          <BaseButton
            v-if="!isRoot"
            variant="ghost"
            :icon="Upload"
            @click="showUpload = true"
          >
            Add New File
          </BaseButton>
        </template>
      </div>
    </div>

    <SearchFilterBar v-model="filters" @search="onSearch" />

    <p v-if="loading" class="text-sm text-[var(--muted)]">Loading documents…</p>
    <p v-else-if="error" class="text-sm text-rose-600">{{ error }}</p>

    <template v-else>
      <div class="space-y-6">
        <div>
          <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-[var(--muted)]">
            Folders
          </h2>
          <div
            v-if="childFolders.length === 0"
            class="surface-card px-4 py-8 text-center text-sm text-[var(--muted)]"
          >
            No folders here yet.
          </div>
          <div v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            <FolderCard
              v-for="child in childFolders"
              :key="child.id"
              kind="folder"
              :item="child"
              @rename="openRename"
              @delete="deleteFolder"
            />
          </div>
        </div>

        <div v-if="!isRoot">
          <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-[var(--muted)]">
            Files
          </h2>
          <div
            v-if="files.length === 0"
            class="surface-card px-4 py-8 text-center text-sm text-[var(--muted)]"
          >
            No files in this folder.
          </div>
          <div v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            <FolderCard
              v-for="file in files"
              :key="file.id"
              kind="file"
              :item="file"
              @delete="deleteFile"
            />
          </div>
        </div>
      </div>
    </template>

    <BaseModal :open="showCreateFolder" title="Create folder" @close="showCreateFolder = false">
      <form class="space-y-5" @submit.prevent="createFolder">
        <label class="field-group">
          <span>Name</span>
          <input v-model="folderName" type="text" required class="field-input" />
        </label>
        <label v-if="isRoot" class="field-group">
          <span>Department</span>
          <select v-model="createDepartmentId" required class="field-input">
            <option
              v-for="department in departments"
              :key="department.id"
              :value="String(department.id)"
            >
              {{ department.name }}
            </option>
          </select>
        </label>
        <div class="flex justify-end gap-2">
          <BaseButton variant="secondary" :icon="X" @click="showCreateFolder = false">
            Cancel
          </BaseButton>
          <BaseButton type="submit" :icon="FolderPlus">Create</BaseButton>
        </div>
      </form>
    </BaseModal>

    <BaseModal :open="showRenameFolder" title="Rename folder" @close="showRenameFolder = false">
      <form class="space-y-5" @submit.prevent="renameFolder">
        <label class="field-group">
          <span>Name</span>
          <input v-model="folderName" type="text" required class="field-input" />
        </label>
        <div class="flex justify-end gap-2">
          <BaseButton variant="secondary" :icon="X" @click="showRenameFolder = false">
            Cancel
          </BaseButton>
          <BaseButton type="submit" :icon="Check">Save</BaseButton>
        </div>
      </form>
    </BaseModal>

    <UploadModal
      :open="showUpload"
      :folder-id="folder?.id ?? folderId"
      :department-id="folder?.department_id"
      @close="showUpload = false"
      @uploaded="onUploaded"
    />
  </section>
</template>
