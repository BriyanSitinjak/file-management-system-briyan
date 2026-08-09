<script setup>
defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})

function crumbTo(crumb) {
  if (crumb.type === 'file' || crumb.to === null) return null
  if (crumb.to) return crumb.to
  if (crumb.id) return `/folders/${crumb.id}`
  return '/folders'
}
</script>

<template>
  <nav class="flex flex-wrap items-center gap-1 text-sm text-[var(--muted)]">
    <template v-for="(crumb, index) in items" :key="`${crumb.type || 'folder'}-${crumb.id ?? 'root'}-${index}`">
      <RouterLink
        v-if="crumbTo(crumb)"
        class="rounded-md px-1.5 py-0.5 hover:bg-[var(--brand-soft)] hover:text-[var(--brand-strong)]"
        :to="crumbTo(crumb)"
      >
        {{ crumb.name }}
      </RouterLink>
      <span v-else class="px-1.5 py-0.5 font-medium text-[var(--ink)]">
        {{ crumb.name }}
      </span>
      <span v-if="index < items.length - 1">/</span>
    </template>
  </nav>
</template>
