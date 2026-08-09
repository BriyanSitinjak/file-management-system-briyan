<script setup>
import { computed } from 'vue'
import { motion } from 'motion-v'
import { fadeUp, transitions } from '../../lib/motion'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  delay: {
    type: Number,
    default: 0,
  },
})

const transition = computed(() => ({
  ...transitions.soft,
  delay: props.delay,
}))
</script>

<template>
  <motion.div
    :initial="fadeUp.initial"
    :animate="fadeUp.animate"
    :exit="fadeUp.exit"
    :transition="transition"
    v-bind="$attrs"
  >
    <slot />
  </motion.div>
</template>
