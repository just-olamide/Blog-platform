import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Import existing components
import HomeView from '@/views/HomeView.vue'
import LoginView from '@/views/auth/LoginView.vue'
import RegisterView from '@/views/auth/RegisterView.vue'
import CreatePostView from '@/views/CreatePostView.vue'
import PostView from '@/views/PostView.vue'
import EditPostView from '@/views/EditPostView.vue'
import ProfileView from '@/views/ProfileView.vue'

// Admin components
import AdminDashboard from '@/views/admin/AdminDashboard.vue'
// TODO: Create these components later
// import AdminPosts from '@/views/admin/AdminPosts.vue'
// import AdminUsers from '@/views/admin/AdminUsers.vue'
// import AdminComments from '@/views/admin/AdminComments.vue'
// import AdminLogs from '@/views/admin/AdminLogs.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { requiresGuest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { requiresGuest: true }
    },
    {
      path: '/create-post',
      name: 'create-post',
      component: CreatePostView,
      meta: { requiresAuth: true, adminRestricted: true }
    },
    {
      path: '/posts/:id',
      name: 'post',
      component: PostView,
      props: true
    },
    {
      path: '/posts/:id/edit',
      name: 'edit-post',
      component: EditPostView,
      props: true,
      meta: { requiresAuth: true }
    },
    {
      path: '/profile/:id?',
      name: 'profile',
      component: ProfileView,
      props: true,
      meta: { requiresAuth: true, adminRestricted: true }
    },
    {
      path: '/admin',
      name: 'admin',
      redirect: '/admin/dashboard',
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/dashboard',
      name: 'admin-dashboard',
      component: AdminDashboard,
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    // TODO: Uncomment these routes when components are created
    // {
    //   path: '/admin/posts',
    //   name: 'admin-posts',
    //   component: AdminPosts,
    //   meta: { requiresAuth: true, requiresAdmin: true }
    // },
    // {
    //   path: '/admin/users',
    //   name: 'admin-users',
    //   component: AdminUsers,
    //   meta: { requiresAuth: true, requiresAdmin: true }
    // },
    // {
    //   path: '/admin/comments',
    //   name: 'admin-comments',
    //   component: AdminComments,
    //   meta: { requiresAuth: true, requiresAdmin: true }
    // },
    // {
    //   path: '/admin/logs',
    //   name: 'admin-logs',
    //   component: AdminLogs,
    //   meta: { requiresAuth: true, requiresAdmin: true }
    // }
  ]
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
    return
  }
  
  // Check if route requires guest (not authenticated)
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/')
    return
  }
  
  // Check if route requires admin
  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next('/')
    return
  }
  
  // Check if route is restricted for admins
  if (to.meta.adminRestricted && authStore.user?.role === 'admin') {
    next('/admin')
    return
  }
  
  next()
})

export default router
