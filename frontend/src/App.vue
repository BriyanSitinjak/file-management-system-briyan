<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { AnimatePresence, motion } from 'motion-v'
import AppLayout from './layouts/AppLayout.vue'
import FeedbackModal from './components/FeedbackModal.vue'
import ToastStack from './components/ToastStack.vue'
import { fadeUp, transitions } from './lib/motion'

const route = useRoute()
const showLayout = computed(() => !route.meta.guest)
</script>

<template>
  <AppLayout v-if="showLayout">
    <RouterView v-slot="{ Component, route: currentRoute }">
      <AnimatePresence mode="wait">
        <motion.div
          :key="currentRoute.fullPath"
          :initial="fadeUp.initial"
          :animate="fadeUp.animate"
          :exit="fadeUp.exit"
          :transition="transitions.soft"
        >
          <component :is="Component" />
        </motion.div>
      </AnimatePresence>
    </RouterView>
  </AppLayout>

  <RouterView v-else v-slot="{ Component, route: currentRoute }">
    <AnimatePresence mode="wait">
      <motion.div
        :key="currentRoute.fullPath"
        :initial="fadeUp.initial"
        :animate="fadeUp.animate"
        :exit="fadeUp.exit"
        :transition="transitions.soft"
      >
        <component :is="Component" />
      </motion.div>
    </AnimatePresence>
  </RouterView>

  <FeedbackModal />
  <ToastStack />
</template>
