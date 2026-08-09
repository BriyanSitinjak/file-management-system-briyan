<script setup>
import { X } from '@lucide/vue'
import { AnimatePresence, motion } from 'motion-v'
import { useUiStore } from '../stores/ui'
import BaseButton from './BaseButton.vue'
import { fade, scaleIn, transitions } from '../lib/motion'

const ui = useUiStore()
</script>

<template>
  <Teleport to="body">
    <AnimatePresence>
      <motion.div
        v-if="ui.confirmDialog.open"
        key="confirm-modal"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 p-4"
        :initial="fade.initial"
        :animate="fade.animate"
        :exit="fade.exit"
        :transition="transitions.snappy"
        @click.self="ui.closeConfirm(false)"
      >
        <motion.div
          class="surface-card w-full max-w-md p-5"
          role="alertdialog"
          aria-modal="true"
          aria-labelledby="confirm-title"
          :initial="scaleIn.initial"
          :animate="scaleIn.animate"
          :exit="scaleIn.exit"
          :transition="transitions.soft"
        >
          <div class="mb-6 flex items-start gap-3">
            <div class="min-w-0 flex-1 space-y-2">
              <h2 id="confirm-title" class="text-lg font-bold tracking-tight text-[var(--ink)]">
                {{ ui.confirmDialog.title }}
              </h2>
              <p v-if="ui.confirmDialog.message" class="text-sm text-[var(--muted)]">
                {{ ui.confirmDialog.message }}
              </p>
            </div>
            <button
              type="button"
              class="inline-flex rounded-lg p-1 text-[var(--muted)] hover:bg-[var(--canvas)]"
              aria-label="Close"
              @click="ui.closeConfirm(false)"
            >
              <X class="size-4" :stroke-width="2" />
            </button>
          </div>

          <div class="flex justify-end gap-2">
            <BaseButton variant="secondary" @click="ui.closeConfirm(false)">
              {{ ui.confirmDialog.cancelLabel }}
            </BaseButton>
            <BaseButton variant="danger" @click="ui.closeConfirm(true)">
              {{ ui.confirmDialog.confirmLabel }}
            </BaseButton>
          </div>
        </motion.div>
      </motion.div>
    </AnimatePresence>
  </Teleport>
</template>
