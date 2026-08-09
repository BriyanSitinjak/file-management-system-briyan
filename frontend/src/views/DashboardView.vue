<script setup>
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
</script>

<template>
  <section class="space-y-2">
    <h1 class="text-2xl font-semibold">Dashboard</h1>
    <p class="text-slate-600">
      Signed in as {{ auth.user?.name }} ({{ auth.user?.role }})
    </p>
  </section>
</template>
