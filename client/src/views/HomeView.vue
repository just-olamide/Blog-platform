<template>
  <div class="home">
    <!-- Hero Section -->
    <section class="hero-section bg-primary text-white py-5">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <h1 class="display-4 fw-bold mb-3">Welcome to BlogPlatform</h1>
            <p class="lead mb-4">
              Discover amazing stories, share your thoughts, and connect with a community of writers and readers.
            </p>
            <div class="d-flex gap-3">
              <router-link 
                v-if="!authStore.isAuthenticated" 
                to="/register" 
                class="btn btn-light btn-lg"
              >
                Get Started
              </router-link>
              <router-link 
                v-if="authStore.isAuthenticated" 
                to="/create-post" 
                class="btn btn-light btn-lg"
              >
                <i class="bi bi-plus-circle me-2"></i>Create Post
              </router-link>
            </div>
          </div>
          <div class="col-lg-6 text-center">
            <i class="bi bi-journal-bookmark display-1"></i>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <div class="container py-5">
      <div class="row">
        <!-- Main Posts Area -->
        <div class="col-lg-8">
          <!-- Search and Filter Bar -->
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0">Latest Posts</h2>
            <div class="d-flex gap-2">
              <div class="input-group" style="width: 300px;">
                <input
                  type="text"
                  class="form-control"
                  placeholder="Search posts..."
                  v-model="searchQuery"
                  @keyup.enter="handleSearch"
                >
                <button class="btn btn-outline-secondary" @click="handleSearch">
                  <i class="bi bi-search"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Loading Spinner -->
          <div v-if="postsStore.loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>

          <!-- Posts List -->
          <div v-else-if="postsStore.posts.length > 0" class="posts-list">
            <div 
              v-for="post in postsStore.posts" 
              :key="post.id" 
              class="card mb-4 post-card"
            >
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="d-flex align-items-center">
                    <div class="avatar-circle me-3">
                      {{ post.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <h6 class="mb-0">{{ post.user.name }}</h6>
                      <small class="text-muted">{{ formatDate(post.created_at) }}</small>
                    </div>
                  </div>
                  <span class="badge bg-primary">{{ post.status }}</span>
                </div>

                <h5 class="card-title">
                  <router-link :to="`/posts/${post.id}`" class="text-decoration-none">
                    {{ post.title }}
                  </router-link>
                </h5>
                
                <p class="card-text text-muted">
                  {{ truncateContent(post.content, 150) }}
                </p>

                <!-- Post Image -->
                <div v-if="post.image" class="mb-3">
                  <img 
                    :src="`http://localhost:8000/storage/${post.image}`" 
                    :alt="post.title"
                    class="img-fluid rounded"
                    style="max-height: 300px; width: 100%; object-fit: cover;"
                  >
                </div>

                <!-- Post Actions -->
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex gap-3">
                    <button 
                      v-if="authStore.isAuthenticated"
                      @click="toggleLike(post.id)"
                      class="btn btn-sm btn-outline-danger"
                      :class="{ 'active': post.user_liked }"
                    >
                      <i class="bi bi-heart"></i>
                      {{ post.likes_count || 0 }}
                    </button>
                    
                    <router-link 
                      :to="`/posts/${post.id}`" 
                      class="btn btn-sm btn-outline-primary"
                    >
                      <i class="bi bi-chat"></i>
                      {{ post.comments_count || 0 }}
                    </router-link>
                    
                    <button 
                      v-if="authStore.isAuthenticated"
                      @click="toggleSave(post.id)"
                      class="btn btn-sm btn-outline-warning"
                      :class="{ 'active': post.user_saved }"
                    >
                      <i class="bi bi-bookmark"></i>
                      {{ post.saves_count || 0 }}
                    </button>
                    
                    <button 
                      v-if="authStore.isAuthenticated"
                      @click="toggleRepost(post.id)"
                      class="btn btn-sm btn-outline-success"
                      :class="{ 'active': post.user_reposted }"
                    >
                      <i class="bi bi-arrow-repeat"></i>
                      {{ post.reposts_count || 0 }}
                    </button>
                  </div>
                  
                  <router-link 
                    :to="`/posts/${post.id}`" 
                    class="btn btn-primary btn-sm"
                  >
                    Read More
                  </router-link>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <nav v-if="postsStore.pagination.last_page > 1" class="mt-4">
              <ul class="pagination justify-content-center">
                <li class="page-item" :class="{ disabled: postsStore.pagination.current_page === 1 }">
                  <button 
                    class="page-link" 
                    @click="changePage(postsStore.pagination.current_page - 1)"
                    :disabled="postsStore.pagination.current_page === 1"
                  >
                    Previous
                  </button>
                </li>
                
                <li 
                  v-for="page in paginationPages" 
                  :key="page"
                  class="page-item" 
                  :class="{ active: page === postsStore.pagination.current_page }"
                >
                  <button class="page-link" @click="changePage(page)">
                    {{ page }}
                  </button>
                </li>
                
                <li class="page-item" :class="{ disabled: postsStore.pagination.current_page === postsStore.pagination.last_page }">
                  <button 
                    class="page-link" 
                    @click="changePage(postsStore.pagination.current_page + 1)"
                    :disabled="postsStore.pagination.current_page === postsStore.pagination.last_page"
                  >
                    Next
                  </button>
                </li>
              </ul>
            </nav>
          </div>

          <!-- No Posts Message -->
          <div v-else class="text-center py-5">
            <i class="bi bi-journal-x display-1 text-muted"></i>
            <h3 class="mt-3">No posts found</h3>
            <p class="text-muted">
              {{ searchQuery ? 'Try adjusting your search terms.' : 'Be the first to create a post!' }}
            </p>
            <router-link 
              v-if="authStore.isAuthenticated && !searchQuery" 
              to="/create-post" 
              class="btn btn-primary"
            >
              Create First Post
            </router-link>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
          <div class="sticky-top" style="top: 100px;">
            <!-- Quick Stats -->
            <div class="card mb-4">
              <div class="card-header">
                <h6 class="mb-0">Platform Stats</h6>
              </div>
              <div class="card-body">
                <div class="row text-center">
                  <div class="col-6">
                    <h4 class="text-primary mb-0">{{ postsStore.pagination.total || 0 }}</h4>
                    <small class="text-muted">Total Posts</small>
                  </div>
                  <div class="col-6">
                    <h4 class="text-success mb-0">{{ activeUsers }}</h4>
                    <small class="text-muted">Active Users</small>
                  </div>
                </div>
              </div>
            </div>

            <!-- Popular Tags -->
            <div class="card">
              <div class="card-header">
                <h6 class="mb-0">Popular Topics</h6>
              </div>
              <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                  <span class="badge bg-secondary">#Technology</span>
                  <span class="badge bg-secondary">#Lifestyle</span>
                  <span class="badge bg-secondary">#Travel</span>
                  <span class="badge bg-secondary">#Food</span>
                  <span class="badge bg-secondary">#Health</span>
                  <span class="badge bg-secondary">#Business</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth'
import { usePostsStore } from '@/stores/posts'
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export default {
  name: 'HomeView',
  setup() {
    const authStore = useAuthStore()
    const postsStore = usePostsStore()
    const route = useRoute()
    const router = useRouter()
    
    const searchQuery = ref(route.query.search || '')
    const activeUsers = ref(156) // Mock data
    
    const paginationPages = computed(() => {
      const current = postsStore.pagination.current_page
      const last = postsStore.pagination.last_page
      const pages = []
      
      const start = Math.max(1, current - 2)
      const end = Math.min(last, current + 2)
      
      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      
      return pages
    })
    
    const fetchPosts = async (page = 1) => {
      try {
        await postsStore.fetchPosts(page, searchQuery.value)
      } catch (error) {
        console.error('Error fetching posts:', error)
      }
    }
    
    const handleSearch = () => {
      router.push({ query: { search: searchQuery.value || undefined } })
      fetchPosts(1)
    }
    
    const changePage = (page) => {
      if (page >= 1 && page <= postsStore.pagination.last_page) {
        fetchPosts(page)
      }
    }
    
    const toggleLike = async (postId) => {
      try {
        await postsStore.toggleLike(postId)
      } catch (error) {
        console.error('Error toggling like:', error)
      }
    }
    
    const toggleSave = async (postId) => {
      try {
        await postsStore.toggleSave(postId)
      } catch (error) {
        console.error('Error toggling save:', error)
      }
    }
    
    const toggleRepost = async (postId) => {
      try {
        await postsStore.toggleRepost(postId)
      } catch (error) {
        console.error('Error toggling repost:', error)
      }
    }
    
    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }
    
    const truncateContent = (content, maxLength) => {
      if (content.length <= maxLength) return content
      return content.substring(0, maxLength) + '...'
    }
    
    // Watch for route changes
    watch(() => route.query.search, (newSearch) => {
      searchQuery.value = newSearch || ''
      fetchPosts(1)
    })
    
    onMounted(() => {
      fetchPosts(1)
    })
    
    return {
      authStore,
      postsStore,
      searchQuery,
      activeUsers,
      paginationPages,
      handleSearch,
      changePage,
      toggleLike,
      toggleSave,
      toggleRepost,
      formatDate,
      truncateContent
    }
  }
}
</script>

<style scoped>
.hero-section {
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.post-card {
  border: none;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.post-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.avatar-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(45deg, #007bff, #0056b3);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 1.1rem;
}

.btn.active {
  background-color: var(--bs-primary);
  border-color: var(--bs-primary);
  color: white;
}

.pagination .page-link {
  border-radius: 0.375rem;
  margin: 0 2px;
  border: 1px solid #dee2e6;
}

.pagination .page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
}

@media (max-width: 768px) {
  .hero-section {
    text-align: center;
  }
  
  .hero-section .display-4 {
    font-size: 2rem;
  }
  
  .d-flex.gap-3 {
    justify-content: center;
  }
}
</style>
