<script setup>
import { onMounted, ref } from 'vue'
import { Check, Pencil, Plus, Trash2, X } from '@lucide/vue'
import api from '../lib/api'
import { formatDateTime } from '../lib/dates'
import { getErrorMessage } from '../lib/errors'
import { useDepartments } from '../composables/useDepartments'
import { useUiStore } from '../stores/ui'
import BaseButton from '../components/BaseButton.vue'
import BaseModal from '../components/BaseModal.vue'
import DataTable from '../components/DataTable.vue'

const ui = useUiStore()
const { departments: departmentList, loadDepartments } = useDepartments()
const loading = ref(true)
const error = ref('')
const rows = ref([])
const showModal = ref(false)
const editing = ref(null)
const name = ref('')

const columns = [
  { key: 'name', label: 'Name' },
  { key: 'created_at', label: 'Created' },
  { key: 'actions', label: 'Actions' },
]

async function refreshDepartments() {
  loading.value = true
  error.value = ''

  try {
    await loadDepartments(true)
    rows.value = departmentList.value.map((department) => ({
      ...department,
      created_at: formatDateTime(department.created_at),
    }))
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load departments.')
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editing.value = null
  name.value = ''
  showModal.value = true
}

function openEdit(department) {
  editing.value = department
  name.value = department.name
  showModal.value = true
}

async function saveDepartment() {
  if (!name.value.trim()) return

  const isEdit = Boolean(editing.value)

  try {
    if (isEdit) {
      await api.put(`/departments/${editing.value.id}`, { name: name.value.trim() })
    } else {
      await api.post('/departments', { name: name.value.trim() })
    }

    showModal.value = false
    editing.value = null
    name.value = ''
    await refreshDepartments()
    ui.notifySuccess(
      isEdit ? 'Department updated' : 'Department created',
      isEdit ? 'Changes were saved successfully.' : 'The department is ready to use.',
    )
  } catch (err) {
    ui.notifyError(
      isEdit ? 'Could not update department' : 'Could not create department',
      getErrorMessage(err, 'Please try again.'),
    )
  }
}

async function deleteDepartment(department) {
  const confirmed = await ui.confirm({
    title: 'Delete department?',
    message: `"${department.name}" will be permanently removed.`,
    confirmLabel: 'Delete',
  })
  if (!confirmed) return

  try {
    await api.delete(`/departments/${department.id}`)
    await refreshDepartments()
    ui.notifySuccess('Department deleted', `"${department.name}" was removed.`)
  } catch (err) {
    ui.notifyError('Could not delete department', getErrorMessage(err, 'Please try again.'))
  }
}

onMounted(refreshDepartments)
</script>

<template>
  <section class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="page-subtitle">Create, rename, and remove departments.</p>
      </div>
      <BaseButton :icon="Plus" @click="openCreate">Create department</BaseButton>
    </div>

    <p v-if="loading" class="text-sm text-[var(--muted)]">Loading departments…</p>
    <p v-else-if="error" class="text-sm text-rose-600">{{ error }}</p>

    <DataTable
      v-else
      :columns="columns"
      :rows="rows"
      empty-text="No departments yet."
    >
      <template #cell-actions="{ row }">
        <div class="flex gap-2">
          <BaseButton variant="secondary" :icon="Pencil" @click="openEdit(row)">Edit</BaseButton>
          <BaseButton variant="danger" :icon="Trash2" @click="deleteDepartment(row)">
            Delete
          </BaseButton>
        </div>
      </template>
    </DataTable>

    <BaseModal
      :open="showModal"
      :title="editing ? 'Edit department' : 'Create department'"
      @close="showModal = false"
    >
      <form class="space-y-5" @submit.prevent="saveDepartment">
        <label class="field-group">
          <span>Name</span>
          <input
            v-model="name"
            type="text"
            required
            class="field-input"
          />
        </label>
        <div class="flex justify-end gap-2">
          <BaseButton variant="secondary" :icon="X" @click="showModal = false">Cancel</BaseButton>
          <BaseButton type="submit" :icon="Check">Save</BaseButton>
        </div>
      </form>
    </BaseModal>
  </section>
</template>
