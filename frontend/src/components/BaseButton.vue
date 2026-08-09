<!--
  Shared button used across folder, file, and department screens.
  Available to all authenticated roles; variants control visual weight only.
-->
<script setup>
import { motion } from 'motion-v'
import { transitions } from '../lib/motion'

defineProps({
  type: {
    type: String,
    default: 'button',
  },
  variant: {
    type: String,
    default: 'primary',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  icon: {
    type: [Object, Function],
    default: null,
  },
})

const variants = {
  primary: 'bg-[var(--brand)] text-white hover:bg-[var(--brand-strong)]',
  secondary: 'border border-[var(--line)] bg-white text-[var(--ink)] hover:bg-[var(--brand-soft)] dark:bg-[var(--panel)]',
  ghost: 'border border-[var(--line)] bg-transparent text-[var(--ink)] hover:bg-[var(--brand-soft)]',
  danger: 'bg-rose-600 text-white hover:bg-rose-500',
}
</script>

<template>
  <motion.button
    :type="type"
    class="inline-flex items-center justify-center gap-2 rounded-xl px-3.5 py-2 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-50"
    :class="variants[variant] || variants.primary"
    :disabled="disabled"
    :while-hover="disabled ? undefined : { scale: 1.02 }"
    :while-tap="disabled ? undefined : { scale: 0.98 }"
    :transition="transitions.snappy"
  >
    <component :is="icon" v-if="icon" class="size-4 shrink-0" :stroke-width="2" />
    <slot />
  </motion.button>
</template>
