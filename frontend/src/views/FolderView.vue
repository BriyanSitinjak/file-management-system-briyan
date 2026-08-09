<!--
  Core folder browser listing child folders and files for the current folder.
  Administrator and Viewer can browse; Create/Rename/Delete/Upload are admin only.
-->
<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  Check,
  ExternalLink,
  FolderPlus,
  Folder as FolderIcon,
  Pencil,
  Trash2,
  Upload,
  X,
} from '@lucide/vue'
import api from '../lib/api'
import { useAuthStore } from '../stores/auth'
import BaseButton from '../components/BaseButton.vue'
import BaseModal from '../components/BaseModal.vue'
import DataTable from '../components/DataTable.vue'
import SearchFilterBar from '../components/SearchFilterBar.vue'
import UploadModal from '../components/UploadModal.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

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
const departments = ref([])

const folderId = computed(() => route.params.id || null)
const isRoot = computed(() => !folderId.value)

const fileColumns = [
  { key: 'title', label: 'Title' },
  { key: 'department', label: 'Department' },
  { key: 'user', label: 'Uploaded by' },
  { key: 'created_at', label: 'Uploaded' },
  { key: 'actions', label: 'Actions' },
]

// GET /departments for the create-folder department select.
async function loadDepartments() {
  const { data } = await api.get('/departments')
  departments.value = data
  if (!createDepartmentId.value && data[0]) {
    createDepartmentId.value = String(data[0].id)
  }
}

// Load either root children via GET /folders or a folder show via GET /folders/{id}.
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
      // Root listing focuses on folders; files live inside concrete folders.
      files.value = []
    } else {
      const { data } = await api.get(`/folders/${folderId.value}`)
      folder.value = data.folder
      breadcrumbs.value = data.breadcrumbs || []
      childFolders.value = data.folder.children || []
      files.value = mapFiles(data.folder.files || [])

      // Apply client-side filter when viewing a specific folder payload.
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
    error.value = err.response?.data?.message || 'Failed to load folders.'
  } finally {
    loading.value = false
  }
}

// Normalize file rows for the shared table.
function mapFiles(items) {
  return items.map((file) => ({
    ...file,
    department: file.department?.name || '—',
    user: file.user?.name || '—',
    created_at: new Date(file.created_at).toLocaleString(),
  }))
}

// Apply SearchFilterBar values and reload the current folder listing.
function onSearch(nextFilters) {
  filters.value = nextFilters
  loadFolderContents()
}

// POST /folders to create a child folder under the current location.
async function createFolder() {
  if (!folderName.value.trim()) return

  await api.post('/folders', {
    name: folderName.value.trim(),
    department_id: Number(createDepartmentId.value || folder.value?.department_id),
    parent_id: folderId.value ? Number(folderId.value) : null,
  })

  folderName.value = ''
  showCreateFolder.value = false
  await loadFolderContents()
}

// Open the rename modal for a selected folder row.
function openRename(target) {
  renameTarget.value = target
  folderName.value = target.name
  showRenameFolder.value = true
}

// PUT /folders/{id} to rename the selected folder.
async function renameFolder() {
  if (!renameTarget.value || !folderName.value.trim()) return

  await api.put(`/folders/${renameTarget.value.id}`, {
    name: folderName.value.trim(),
  })

  showRenameFolder.value = false
  renameTarget.value = null
  folderName.value = ''
  await loadFolderContents()
}

// DELETE /folders/{id} after confirmation.
async function deleteFolder(target) {
  if (!confirm(`Delete folder "${target.name}"?`)) return
  await api.delete(`/folders/${target.id}`)
  await loadFolderContents()
}

// DELETE /files/{id} after confirmation.
async function deleteFile(target) {
  if (!confirm(`Delete file "${target.title}"?`)) return
  await api.delete(`/files/${target.id}`)
  await loadFolderContents()
}

watch(
  () => route.params.id,
  () => {
    loadFolderContents()
  },
  { immediate: true },
)

loadDepartments()
</script>

<template>
  <section class="space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold">
          {{ folder?.name || 'Folders' }}
        </h1>
        <nav class="mt-2 flex flex-wrap gap-1 text-sm text-slate-500">
          <template v-for="(crumb, index) in breadcrumbs" :key="crumb.id ?? 'root'">
            <RouterLink
              class="hover:underline"
              :to="crumb.id ? `/folders/${crumb.id}` : '/folders'"
            >
              {{ crumb.name }}
            </RouterLink>
            <span v-if="index < breadcrumbs.length - 1">/</span>
          </template>
        </nav>
      </div>

      <!-- Admin only: mutation actions stay hidden for Viewers. -->
      <div v-if="auth.isAdmin" class="flex flex-wrap gap-2">
        <BaseButton :icon="FolderPlus" @click="showCreateFolder = true">
          Create folder
        </BaseButton>
        <!-- Upload needs a concrete folder id, so hide it on the root listing. -->
        <BaseButton
          v-if="!isRoot"
          variant="secondary"
          :icon="Upload"
          @click="showUpload = true"
        >
          Upload file
        </BaseButton>
      </div>
    </div>

    <SearchFilterBar v-model="filters" @search="onSearch" />

    <p v-if="loading" class="text-sm text-slate-500">Loading folder contents…</p>
    <p v-else-if="error" class="text-sm text-red-600">{{ error }}</p>

    <template v-else>
      <div class="space-y-3">
        <h2 class="text-lg font-medium">Child folders</h2>
        <div v-if="childFolders.length === 0" class="text-sm text-slate-500">
          No child folders.
        </div>
        <ul v-else class="divide-y rounded border border-slate-200 bg-white">
          <li
            v-for="child in childFolders"
            :key="child.id"
            class="flex flex-wrap items-center justify-between gap-3 px-3 py-2"
          >
            <RouterLink class="inline-flex items-center gap-2 font-medium underline" :to="`/folders/${child.id}`">
              <FolderIcon class="size-4 shrink-0" :stroke-width="2" />
              {{ child.name }}
            </RouterLink>
            <!-- Admin only: rename and delete for folders. -->
            <div v-if="auth.isAdmin" class="flex gap-2">
              <BaseButton variant="secondary" :icon="Pencil" @click="openRename(child)">
                Rename
              </BaseButton>
              <BaseButton variant="danger" :icon="Trash2" @click="deleteFolder(child)">
                Delete
              </BaseButton>
            </div>
          </li>
        </ul>
      </div>

      <div class="space-y-3">
        <h2 class="text-lg font-medium">Files</h2>
        <DataTable :columns="fileColumns" :rows="files" empty-text="No files in this folder.">
          <template #cell-title="{ row }">
            <RouterLink class="underline" :to="`/files/${row.id}`">{{ row.title }}</RouterLink>
          </template>
          <template #cell-actions="{ row }">
            <div class="flex gap-2">
              <BaseButton
                variant="secondary"
                :icon="ExternalLink"
                @click="router.push(`/files/${row.id}`)"
              >
                Open
              </BaseButton>
              <!-- Admin only: file delete from the folder listing. -->
              <BaseButton
                v-if="auth.isAdmin"
                variant="danger"
                :icon="Trash2"
                @click="deleteFile(row)"
              >
                Delete
              </BaseButton>
            </div>
          </template>
        </DataTable>
      </div>
    </template>

    <BaseModal :open="showCreateFolder" title="Create folder" @close="showCreateFolder = false">
      <form class="space-y-4" @submit.prevent="createFolder">
        <label class="block space-y-1 text-sm">
          <span>Name</span>
          <input
            v-model="folderName"
            type="text"
            required
            class="w-full rounded border border-slate-300 px-3 py-2"
          />
        </label>
        <!-- Department select is needed when creating under root with no inherited department. -->
        <label v-if="isRoot" class="block space-y-1 text-sm">
          <span>Department</span>
          <select
            v-model="createDepartmentId"
            required
            class="w-full rounded border border-slate-300 px-3 py-2"
          >
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
      <form class="space-y-4" @submit.prevent="renameFolder">
        <label class="block space-y-1 text-sm">
          <span>Name</span>
          <input
            v-model="folderName"
            type="text"
            required
            class="w-full rounded border border-slate-300 px-3 py-2"
          />
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
      :folder-id="folderId"
      :department-id="folder?.department_id"
      @close="showUpload = false"
      @uploaded="loadFolderContents"
    />
  </section>
</template>
