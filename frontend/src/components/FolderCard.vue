<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { FileText, Folder, MoreVertical, Pencil, Trash2 } from '@lucide/vue'
import { AnimatePresence, motion } from 'motion-v'
import { formatDate } from '../lib/dates'
import { useAuthStore } from '../stores/auth'
import { scaleIn, transitions } from '../lib/motion'

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  kind: {
    type: String,
    default: 'folder', // folder | file
  },
})

const emit = defineEmits(['rename', 'delete'])

const auth = useAuthStore()
const router = useRouter()
const menuOpen = ref(false)

const isFolder = computed(() => props.kind === 'folder')
const title = computed(() => (isFolder.value ? props.item.name : props.item.title))
const subtitle = computed(() => {
  if (isFolder.value) {
    return props.item.department?.name || 'Folder'
  }
  return props.item.mime_type?.split('/')[1]?.toUpperCase() || 'FILE'
})
const modified = computed(() => {
  const value = props.item.updated_at || props.item.created_at
  return value ? `Modified ${formatDate(value)}` : ''
})

function openItem() {
  if (isFolder.value) {
    router.push(`/folders/${props.item.id}`)
    return
  }
  router.push(`/files/${props.item.id}`)
}

function onRename() {
  menuOpen.value = false
  emit('rename', props.item)
}

function onDelete() {
  menuOpen.value = false
  emit('delete', props.item)
}
</script>

<template>
  <motion.article
    class="surface-card relative flex min-h-44 cursor-pointer flex-col p-4"
    :while-hover="{ y: -3, scale: 1.01 }"
    :transition="transitions.snappy"
    @click="openItem"
  >
    <button
      v-if="auth.isAdmin"
      type="button"
      class="absolute right-3 top-3 rounded-lg p-1 text-[var(--muted)] hover:bg-[var(--canvas)]"
      @click.stop="menuOpen = !menuOpen"
    >
      <MoreVertical class="size-4" :stroke-width="2" />
    </button>

    <AnimatePresence>
      <motion.div
        v-if="menuOpen && auth.isAdmin"
        key="menu"
        class="absolute right-3 top-10 z-10 min-w-36 rounded-xl bg-slate-800 p-2 text-white shadow-xl"
        :initial="scaleIn.initial"
        :animate="scaleIn.animate"
        :exit="scaleIn.exit"
        :transition="transitions.snappy"
        @click.stop
      >
        <button
          v-if="isFolder"
          type="button"
          class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-left text-sm hover:bg-white/10"
          @click="onRename"
        >
          <Pencil class="size-3.5" :stroke-width="2" />
          Rename
        </button>
        <button
          type="button"
          class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-left text-sm hover:bg-white/10"
          @click="onDelete"
        >
          <Trash2 class="size-3.5" :stroke-width="2" />
          Delete
        </button>
      </motion.div>
    </AnimatePresence>

    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--brand-soft)] text-[var(--brand-strong)]">
      <Folder v-if="isFolder" class="size-7" :stroke-width="1.75" />
      <FileText v-else class="size-7" :stroke-width="1.75" />
    </div>

    <p class="text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">
      {{ subtitle }}
    </p>
    <h3 class="mt-1 line-clamp-2 text-lg font-bold tracking-tight text-[var(--ink)]">
      {{ title }}
    </h3>
    <p class="mt-auto pt-3 text-xs text-[var(--muted)]">{{ modified }}</p>
  </motion.article>
</template>
