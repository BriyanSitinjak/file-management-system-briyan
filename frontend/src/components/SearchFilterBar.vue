<script setup>
import { ref, watch } from 'vue'
import { Eraser, Search } from '@lucide/vue'
import { useDepartments } from '../composables/useDepartments'
import BaseButton from './BaseButton.vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ q: '', department_id: '' }),
  },
})

const emit = defineEmits(['update:modelValue', 'search'])

const { departments, loadDepartments } = useDepartments()
const local = ref({
  q: props.modelValue.q || '',
  department_id: props.modelValue.department_id || '',
})

function applyFilters() {
  const next = {
    q: local.value.q,
    department_id: local.value.department_id,
  }
  emit('update:modelValue', next)
  emit('search', next)
}

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
  <form
    class="surface-card flex flex-wrap items-end gap-3 p-3"
    @submit.prevent="applyFilters"
  >
    <label class="field-group min-w-48 flex-1">
      <span>Document name</span>
      <div class="relative">
        <Search class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-[var(--muted)]" :stroke-width="2" />
        <input
          v-model="local.q"
          type="search"
          placeholder="Search by name or title"
          class="field-input search-input"
        />
      </div>
    </label>

    <label class="field-group min-w-40">
      <span>Department</span>
      <select v-model="local.department_id" class="field-input">
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

    <BaseButton type="submit" :icon="Search">Apply</BaseButton>
    <BaseButton type="button" variant="ghost" :icon="Eraser" @click="clearFilters">
      Clear
    </BaseButton>
  </form>
</template>
