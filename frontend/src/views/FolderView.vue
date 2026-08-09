<!--
  Core folder browser listing child folders and files for the current folder.
  Administrator and Viewer can browse; Create/Rename/Delete/Upload are admin only.
-->
<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import {
  Check,
  FolderPlus,
  LayoutGrid,
  List,
  Upload,
  X,
} from '@lucide/vue'
import api from '../lib/api'
import { useAuthStore } from '../stores/auth'
import BaseButton from '../components/BaseButton.vue'
import BaseModal from '../components/BaseModal.vue'
import DataTable from '../components/DataTable.vue'
import FolderCard from '../components/FolderCard.vue'
import SearchFilterBar from '../components/SearchFilterBar.vue'
import UploadModal from '../components/UploadModal.vue'

const auth = useAuthStore()
const route = useRoute()

const loading = ref(false)
const error = ref('')
const folder = ref(null)
const breadcrumbs = ref([])
const childFolders = ref([])
const files = ref([])
const filters = ref({ q: '', department_id: '' })
const viewMode = ref('grid')

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
    error.value = err.response?.data?.message || 'Failed to load folders.'
  } finally {
    loading.value = false
  }
}

function mapFiles(items) {
  return items.map((file) => ({
    ...file,
    department: file.department?.name || '—',
    user: file.user?.name || '—',
    created_at: new Date(file.created_at).toLocaleString(),
  }))
}

function onSearch(nextFilters) {
  filters.value = nextFilters
  loadFolderContents()
}

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

function openRename(target) {
  renameTarget.value = target
  folderName.value = target.name
  showRenameFolder.value = true
}

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

async function deleteFolder(target) {
  if (!confirm(`Delete folder "${target.name}"?`)) return
  await api.delete(`/folders/${target.id}`)
  await loadFolderContents()
}

async function deleteFile(target) {
  if (!confirm(`Delete file "${target.title}"?`)) return
  await api.delete(`/files/${target.id}`)
  await loadFolderContents()
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

loadDepartments()
</script>

<template>
  <section class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <nav class="flex flex-wrap items-center gap-1 text-sm text-[var(--muted)]">
        <template v-for="(crumb, index) in breadcrumbs" :key="crumb.id ?? 'root'">
          <RouterLink
            class="rounded-md px-1.5 py-0.5 hover:bg-[var(--brand-soft)] hover:text-[var(--brand-strong)]"
            :to="crumb.id ? `/folders/${crumb.id}` : '/folders'"
          >
            {{ crumb.name }}
          </RouterLink>
          <span v-if="index < breadcrumbs.length - 1">/</span>
        </template>
      </nav>

      <div class="flex flex-wrap items-center gap-2">
        <!-- Admin only: mutation actions stay hidden for Viewers. -->
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

        <div class="inline-flex rounded-xl border border-[var(--line)] bg-[var(--panel)] p-1">
          <button
            type="button"
            class="rounded-lg p-1.5"
            :class="viewMode === 'list' ? 'bg-[var(--brand-soft)] text-[var(--brand-strong)]' : 'text-[var(--muted)]'"
            @click="viewMode = 'list'"
          >
            <List class="size-4" :stroke-width="2" />
          </button>
          <button
            type="button"
            class="rounded-lg p-1.5"
            :class="viewMode === 'grid' ? 'bg-[var(--brand-soft)] text-[var(--brand-strong)]' : 'text-[var(--muted)]'"
            @click="viewMode = 'grid'"
          >
            <LayoutGrid class="size-4" :stroke-width="2" />
          </button>
        </div>
      </div>
    </div>

    <SearchFilterBar v-model="filters" @search="onSearch" />

    <p v-if="loading" class="text-sm text-[var(--muted)]">Loading documents…</p>
    <p v-else-if="error" class="text-sm text-rose-600">{{ error }}</p>

    <template v-else>
      <div v-if="viewMode === 'grid'" class="space-y-6">
        <div>
          <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-[var(--muted)]">
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
              v-for="(child, index) in childFolders"
              :key="child.id"
              kind="folder"
              :item="child"
              :tone-index="index"
              @rename="openRename"
              @delete="deleteFolder"
            />
          </div>
        </div>

        <div v-if="!isRoot">
          <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-[var(--muted)]">
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
              v-for="(file, index) in files"
              :key="file.id"
              kind="file"
              :item="file"
              :tone-index="index + 2"
              @delete="deleteFile"
            />
          </div>
        </div>
      </div>

      <div v-else class="space-y-4">
        <DataTable
          :columns="[
            { key: 'name', label: 'Name' },
            { key: 'type', label: 'Type' },
            { key: 'department', label: 'Department' },
            { key: 'updated', label: 'Modified' },
          ]"
          :rows="[
            ...childFolders.map((item) => ({
              id: `folder-${item.id}`,
              name: item.name,
              type: 'Folder',
              department: item.department?.name || '—',
              updated: new Date(item.updated_at || item.created_at).toLocaleDateString(),
              href: `/folders/${item.id}`,
            })),
            ...files.map((item) => ({
              id: `file-${item.id}`,
              name: item.title,
              type: 'File',
              department: item.department || '—',
              updated: new Date(item.updated_at || item.created_at).toLocaleDateString(),
              href: `/files/${item.id}`,
            })),
          ]"
          empty-text="No documents found."
        >
          <template #cell-name="{ row }">
            <RouterLink class="font-semibold text-[var(--brand-strong)] hover:underline" :to="row.href">
              {{ row.name }}
            </RouterLink>
          </template>
        </DataTable>
      </div>
    </template>

    <BaseModal :open="showCreateFolder" title="Create folder" @close="showCreateFolder = false">
      <form class="space-y-4" @submit.prevent="createFolder">
        <label class="block space-y-1 text-sm">
          <span>Name</span>
          <input v-model="folderName" type="text" required class="field-input" />
        </label>
        <label v-if="isRoot" class="block space-y-1 text-sm">
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
      <form class="space-y-4" @submit.prevent="renameFolder">
        <label class="block space-y-1 text-sm">
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
      :folder-id="folderId"
      :department-id="folder?.department_id"
      @close="showUpload = false"
      @uploaded="loadFolderContents"
    />
  </section>
</template>
