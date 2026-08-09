<!--
  Shared modal shell for create/edit dialogs (upload, folder rename, departments).
  Visible to whoever opens it; parent screens gate open actions by role.
-->
<script setup>
defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['close'])

// Close when the backdrop is clicked.
function onBackdropClick() {
  emit('close')
}
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
      @click.self="onBackdropClick"
    >
      <div class="w-full max-w-lg rounded-lg bg-white p-5 shadow-lg">
        <div class="mb-4 flex items-center justify-between gap-3">
          <h2 class="text-lg font-semibold text-slate-900">{{ title }}</h2>
          <button
            type="button"
            class="rounded px-2 py-1 text-sm text-slate-500 hover:bg-slate-100"
            @click="emit('close')"
          >
            Close
          </button>
        </div>
        <slot />
      </div>
    </div>
  </Teleport>
</template>
