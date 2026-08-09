<!--
  Shared modal shell for create/edit dialogs (upload, folder rename, departments).
  Visible to whoever opens it; parent screens gate open actions by role.
-->
<script setup>
import { X } from '@lucide/vue'
import { AnimatePresence, motion } from 'motion-v'
import { fade, scaleIn, transitions } from '../lib/motion'

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
    <AnimatePresence>
      <motion.div
        v-if="open"
        key="modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        :initial="fade.initial"
        :animate="fade.animate"
        :exit="fade.exit"
        :transition="transitions.snappy"
        @click.self="onBackdropClick"
      >
        <motion.div
          class="w-full max-w-lg rounded-lg bg-white p-5 shadow-lg dark:bg-slate-900"
          :initial="scaleIn.initial"
          :animate="scaleIn.animate"
          :exit="scaleIn.exit"
          :transition="transitions.soft"
        >
          <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ title }}</h2>
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded px-2 py-1 text-sm text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
              @click="emit('close')"
            >
              <X class="size-4" :stroke-width="2" />
              Close
            </button>
          </div>
          <slot />
        </motion.div>
      </motion.div>
    </AnimatePresence>
  </Teleport>
</template>
