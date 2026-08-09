<script setup>
import { AlertCircle, CheckCircle2, X } from '@lucide/vue'
import { AnimatePresence, motion } from 'motion-v'
import { useUiStore } from '../stores/ui'
import { fadeUp, transitions } from '../lib/motion'

const ui = useUiStore()
</script>

<template>
  <Teleport to="body">
    <div
      class="pointer-events-none fixed inset-x-0 top-4 z-[70] flex flex-col items-center gap-2 px-4 sm:items-end sm:px-6"
      aria-live="polite"
      aria-relevant="additions"
    >
      <AnimatePresence>
        <motion.div
          v-for="toast in ui.toasts"
          :key="toast.id"
          class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl border px-4 py-3 shadow-lg"
          :class="
            toast.type === 'error'
              ? 'border-rose-200 bg-white text-[var(--ink)] dark:border-rose-900 dark:bg-[var(--panel)]'
              : 'border-[var(--line)] bg-white text-[var(--ink)] dark:bg-[var(--panel)]'
          "
          role="status"
          :initial="fadeUp.initial"
          :animate="fadeUp.animate"
          :exit="fadeUp.exit"
          :transition="transitions.snappy"
        >
          <CheckCircle2
            v-if="toast.type === 'success'"
            class="mt-0.5 size-5 shrink-0 text-[var(--brand-strong)]"
            :stroke-width="2"
          />
          <AlertCircle
            v-else
            class="mt-0.5 size-5 shrink-0 text-rose-600"
            :stroke-width="2"
          />

          <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold">{{ toast.title }}</p>
            <p v-if="toast.message" class="mt-0.5 text-sm text-[var(--muted)]">
              {{ toast.message }}
            </p>
          </div>

          <button
            type="button"
            class="rounded-lg p-1 text-[var(--muted)] hover:bg-[var(--canvas)]"
            aria-label="Dismiss notification"
            @click="ui.dismissToast(toast.id)"
          >
            <X class="size-4" :stroke-width="2" />
          </button>
        </motion.div>
      </AnimatePresence>
    </div>
  </Teleport>
</template>
