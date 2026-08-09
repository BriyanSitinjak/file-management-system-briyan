import { ref } from 'vue'
import api from '../lib/api'

const departments = ref([])
const loaded = ref(false)
let pending = null

/** Shared cached department list (avoids repeat GET /departments). */
export function useDepartments() {
  async function loadDepartments(force = false) {
    if (loaded.value && !force) return departments.value
    if (pending) return pending

    pending = api
      .get('/departments')
      .then(({ data }) => {
        departments.value = data
        loaded.value = true
        return departments.value
      })
      .finally(() => {
        pending = null
      })

    return pending
  }

  return {
    departments,
    loadDepartments,
  }
}
