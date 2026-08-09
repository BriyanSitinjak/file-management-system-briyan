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
          class="surface-card w-full max-w-lg p-5"
          :initial="scaleIn.initial"
          :animate="scaleIn.animate"
          :exit="scaleIn.exit"
          :transition="transitions.soft"
        >
          <div class="mb-6 flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold tracking-tight text-[var(--ink)]">{{ title }}</h2>
            <button
              type="button"
              class="inline-flex rounded-lg p-1 text-[var(--muted)] hover:bg-[var(--canvas)]"
              aria-label="Close"
              @click="emit('close')"
            >
              <X class="size-4" :stroke-width="2" />
            </button>
          </div>
          <slot />
        </motion.div>
      </motion.div>
    </AnimatePresence>
  </Teleport>
</template>
