<!--
  Shared button used across folder, file, and department screens.
  Available to all authenticated roles; variants control visual weight only.
-->
<script setup>
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
})

const variants = {
  primary: 'bg-slate-900 text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white',
  secondary: 'border border-slate-300 bg-white text-slate-800 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700',
  danger: 'bg-red-600 text-white hover:bg-red-500',
}
</script>

<template>
  <button
    :type="type"
    class="inline-flex items-center justify-center rounded px-3 py-2 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-50"
    :class="variants[variant] || variants.primary"
    :disabled="disabled"
  >
    <slot />
  </button>
</template>
