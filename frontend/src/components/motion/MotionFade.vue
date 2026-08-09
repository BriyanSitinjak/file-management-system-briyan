<!--
  Thin wrapper around motion-v so screens can fade/slide content
  without repeating animation props everywhere.
-->
<script setup>
import { computed } from 'vue'
import { motion } from 'motion-v'
import { fade, fadeUp, scaleIn, slideDown, transitions } from '../../lib/motion'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  variant: {
    type: String,
    default: 'fadeUp',
  },
  delay: {
    type: Number,
    default: 0,
  },
})

const presets = { fade, fadeUp, scaleIn, slideDown }

const active = computed(() => presets[props.variant] || fadeUp)
const transition = computed(() => ({
  ...transitions.soft,
  delay: props.delay,
}))
</script>

<template>
  <motion.div
    :initial="active.initial"
    :animate="active.animate"
    :exit="active.exit"
    :transition="transition"
    v-bind="$attrs"
  >
    <slot />
  </motion.div>
</template>
