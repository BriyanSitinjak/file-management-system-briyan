<!--
  Text and department filter bar for folder/file index screens.
  Available to Administrator and Viewer; emits filters for parent API calls.
-->
<script setup>
import { ref, watch } from 'vue'
import api from '../lib/api'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ q: '', department_id: '' }),
  },
})

const emit = defineEmits(['update:modelValue', 'search'])

const departments = ref([])
const local = ref({
  q: props.modelValue.q || '',
  department_id: props.modelValue.department_id || '',
})

// GET /departments so the filter dropdown has options.
async function loadDepartments() {
  const { data } = await api.get('/departments')
  departments.value = data
}

// Push current filter values to the parent and trigger a search.
function applyFilters() {
  const next = {
    q: local.value.q,
    department_id: local.value.department_id,
  }
  emit('update:modelValue', next)
  emit('search', next)
}

// Reset filters and ask the parent to reload unfiltered data.
function clearFilters() {
  local.value = { q: '', department_id: '' }
  applyFilters()
}

watch(
  () => props.modelValue,
  (value) => {
    local.value = {
      q: value.q || '',
      department_id: value.department_id || '',
    }
  },
  { deep: true },
)

loadDepartments()
</script>

<template>
  <form class="flex flex-wrap items-end gap-3" @submit.prevent="applyFilters">
    <label class="min-w-48 flex-1 space-y-1 text-sm">
      <span>Search</span>
      <input
        v-model="local.q"
        type="search"
        placeholder="Name or title"
        class="w-full rounded border border-slate-300 px-3 py-2"
      />
    </label>

    <label class="min-w-40 space-y-1 text-sm">
      <span>Department</span>
      <select
        v-model="local.department_id"
        class="w-full rounded border border-slate-300 px-3 py-2"
      >
        <option value="">All departments</option>
        <option
          v-for="department in departments"
          :key="department.id"
          :value="String(department.id)"
        >
          {{ department.name }}
        </option>
      </select>
    </label>

    <button type="submit" class="rounded bg-slate-900 px-3 py-2 text-sm text-white">
      Apply
    </button>
    <button
      type="button"
      class="rounded border border-slate-300 px-3 py-2 text-sm"
      @click="clearFilters"
    >
      Clear
    </button>
  </form>
</template>
