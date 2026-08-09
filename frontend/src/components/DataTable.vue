<!--
  Shared table for listing files, departments, and dashboard rows.
  Usable by Administrator and Viewer; action columns are provided by the parent.
-->
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
  <div class="overflow-x-auto rounded border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
    <table class="min-w-full text-left text-sm">
      <thead class="bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
        <tr>
          <th
            v-for="column in columns"
            :key="column.key"
            class="px-3 py-2 font-medium"
          >
            {{ column.label }}
          </th>
        </tr>
      </thead>
      <tbody>
        <!-- Empty state when the parent has no rows to show. -->
        <tr v-if="rows.length === 0">
          <td :colspan="columns.length" class="px-3 py-6 text-center text-slate-500">
            {{ emptyText }}
          </td>
        </tr>
        <tr
          v-for="(row, index) in rows"
          :key="row.id ?? index"
          class="border-t border-slate-100 dark:border-slate-800"
        >
          <td
            v-for="column in columns"
            :key="column.key"
            class="px-3 py-2 text-slate-800 dark:text-slate-100"
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
