<template>
  <div id="app">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
      <div class="container">
        <router-link to="/" class="navbar-brand fw-bold">
          <i class="bi bi-journal-text me-2"></i>
          BlogPlatform
        </router-link>
        
        <button 
          class="navbar-toggler" 
          type="button" 
          data-bs-toggle="collapse" 
          data-bs-target="#navbarNav"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <router-link to="/" class="nav-link">
                <i class="bi bi-house me-1"></i>Home
              </router-link>
            </li>
            <li class="nav-item" v-if="authStore.isAuthenticated && authStore.user?.role !== 'admin'">
              <router-link to="/create-post" class="nav-link">
                <i class="bi bi-plus-circle me-1"></i>Create Post
              </router-link>
            </li>
            <!-- TODO: Uncomment when AdminDashboard component is created -->
            <!-- <li class="nav-item" v-if="authStore.isAdmin">
              <router-link to="/admin/dashboard" class="nav-link">
                <i class="bi bi-speedometer2 me-1"></i>Admin
              </router-link>
            </li> -->
          </ul>
          
          <!-- Search Bar -->
          <form class="d-flex me-3" @submit.prevent="handleSearch">
            <input 
              class="form-control me-2" 
              type="search" 
              placeholder="Search posts..." 
              v-model="searchQuery"
            >
            <button class="btn btn-outline-light" type="submit">
              <i class="bi bi-search"></i>
            </button>
          </form>
          
          <!-- User Menu -->
          <ul class="navbar-nav">
            <li class="nav-item dropdown" v-if="authStore.isAuthenticated">
              <a 
                class="nav-link dropdown-toggle d-flex align-items-center" 
                href="#" 
                role="button" 
                data-bs-toggle="dropdown"
              >
                <i class="bi bi-person-circle me-1"></i>
                {{ authStore.userName }}
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li v-if="authStore.user?.role === 'admin'">
                  <router-link to="/admin" class="dropdown-item">
                    <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                  </router-link>
                </li>
                <li v-if="!authStore.user?.role || authStore.user.role !== 'admin'">
                  <router-link to="/profile" class="dropdown-item">
                    <i class="bi bi-person me-2"></i>Profile
                  </router-link>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item text-danger" href="#" @click="handleLogout">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                  </a>
                </li>
              </ul>
            </li>
            
            <!-- Guest Menu -->
            <template v-else>
              <li class="nav-item">
                <router-link to="/login" class="nav-link">
                  <i class="bi bi-box-arrow-in-right me-1"></i>Login
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/register" class="nav-link">
                  <i class="bi bi-person-plus me-1"></i>Register
                </router-link>
              </li>
            </template>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1">
      <!-- Error Alert -->
      <div v-if="error" class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle me-2"></i>
          {{ error }}
          <button type="button" class="btn-close" @click="clearError"></button>
        </div>
      </div>
      
      <!-- Router View -->
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <h5>BlogPlatform</h5>
            <p class="mb-0">A modern, full-stack blogging platform built with Laravel & Vue.js</p>
          </div>
          <div class="col-md-6 text-md-end">
            <p class="mb-0">&copy; 2024 BlogPlatform. All rights reserved.</p>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth'
import { usePostsStore } from '@/stores/posts'
import { computed, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'App',
  setup() {
    const authStore = useAuthStore()
    const postsStore = usePostsStore()
    const router = useRouter()
    
    const searchQuery = ref('')
    
    const error = computed(() => {
      return authStore.error || postsStore.error
    })
    
    const handleSearch = () => {
      if (searchQuery.value.trim()) {
        router.push({ name: 'home', query: { search: searchQuery.value.trim() } })
      }
    }
    
    const handleLogout = async () => {
      try {
        await authStore.logout()
        router.push('/')
      } catch (error) {
        console.error('Logout error:', error)
      }
    }
    
    const clearError = () => {
      authStore.clearError()
      postsStore.clearError()
    }
    
    onMounted(() => {
      // Fetch user data if token exists
      if (authStore.token) {
        authStore.fetchUser()
      }
    })
    
    return {
      authStore,
      postsStore,
      searchQuery,
      error,
      handleSearch,
      handleLogout,
      clearError
    }
  }
}
</script>

<style>
/* Bootstrap Icons */
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css');

/* Custom Styles */
#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Custom Button Styles */
.btn-primary {
  background: linear-gradient(45deg, #007bff, #0056b3);
  border: none;
}

.btn-primary:hover {
  background: linear-gradient(45deg, #0056b3, #004085);
  transform: translateY(-1px);
}

/* Card Hover Effects */
.card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
</style>