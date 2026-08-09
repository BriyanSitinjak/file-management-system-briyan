<script setup>
defineProps({
  columns: {
    type: Array,
    default: () => [],
  },
  rows: {
    type: Array,
    default: () => [],
  },
  emptyText: {
    type: String,
    default: 'No records found.',
  },
})
</script>

<template>
  <div class="surface-card overflow-x-auto">
    <table class="min-w-full text-left text-sm">
      <thead class="bg-[var(--canvas)] text-[var(--muted)]">
        <tr>
          <th
            v-for="column in columns"
            :key="column.key"
            class="px-4 py-3 font-semibold"
          >
            {{ column.label }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="rows.length === 0">
          <td :colspan="columns.length" class="px-4 py-8 text-center text-[var(--muted)]">
            {{ emptyText }}
          </td>
        </tr>
        <tr
          v-for="(row, index) in rows"
          :key="row.id ?? index"
          class="border-t border-[var(--line)]"
        >
          <td
            v-for="column in columns"
            :key="column.key"
            class="px-4 py-3 text-[var(--ink)]"
          >
            <slot :name="`cell-${column.key}`" :row="row">
              {{ row[column.key] }}
            </slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
