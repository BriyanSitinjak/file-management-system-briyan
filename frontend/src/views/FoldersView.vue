<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const folderId = computed(() => route.params.id || 'root')
</script>

<template>
  <section class="space-y-2">
    <h1 class="text-2xl font-semibold">Folders</h1>
    <p class="text-slate-600">Current folder: {{ folderId }}</p>
  </section>
</template>
