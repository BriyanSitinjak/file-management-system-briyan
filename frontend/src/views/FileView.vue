<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const fileId = computed(() => route.params.id)
</script>

<template>
  <section class="space-y-2">
    <h1 class="text-2xl font-semibold">File</h1>
    <p class="text-slate-600">File id: {{ fileId }}</p>
  </section>
</template>
