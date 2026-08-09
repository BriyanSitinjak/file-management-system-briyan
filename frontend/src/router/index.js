import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { guest: true },
    },
    {
      path: '/',
      name: 'dashboard',
      component: () => import('../views/DashboardView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/folders/:id?',
      name: 'folders',
      component: () => import('../views/FolderView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/files/:id',
      name: 'files',
      component: () => import('../views/FileDetailView.vue'),
      meta: { requiresAuth: true },
    },
    // Admin only: departments management. Mark future create/edit routes with requiresAdmin too.
    {
      path: '/departments',
      name: 'departments',
      component: () => import('../views/DepartmentView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: '/activity',
      name: 'activity',
      component: () => import('../views/ActivityLogView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: '/trash',
      name: 'trash',
      component: () => import('../views/TrashView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
  ],
})

// Navigation guard checks, in order:
// 1) guest routes: redirect authenticated users away from /login
// 2) auth routes: require a token, otherwise send the user to /login
// 3) session hydrate: if token exists but user is missing, call fetchMe()
// 4) admin routes: /departments (and any route with requiresAdmin) require isAdmin
router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (to.meta.guest && auth.token) {
    return { name: 'dashboard' }
  }

  if (to.meta.requiresAuth && !auth.token) {
    return {
      name: 'login',
      query: { redirect: to.fullPath },
    }
  }

  if (auth.token && !auth.user && !to.meta.guest) {
    try {
      await auth.fetchMe()
    } catch {
      auth.clearAuth()
      return {
        name: 'login',
        query: { redirect: to.fullPath },
      }
    }
  }

  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return { name: 'dashboard' }
  }

  return true
})

export default router
