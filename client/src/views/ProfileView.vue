<template>
  <div class="profile-page">
    <div class="container py-5">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading profile...</span>
        </div>
        <p class="mt-3 text-muted">Loading user profile...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ error }}
      </div>

      <!-- Profile Content -->
      <div v-else-if="profileUser" class="row">
        <!-- Profile Header -->
        <div class="col-12">
          <div class="card shadow-sm mb-4">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <div class="d-flex align-items-center">
                    <!-- Profile Avatar -->
                    <div class="profile-avatar me-4">
                      <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center">
                        <i class="bi bi-person-fill fs-1"></i>
                      </div>
                    </div>
                    
                    <!-- Profile Info -->
                    <div>
                      <h1 class="profile-name mb-2">{{ profileUser.name }}</h1>
                      <p class="profile-email text-muted mb-2">
                        <i class="bi bi-envelope me-2"></i>{{ profileUser.email }}
                      </p>
                      <div class="profile-stats d-flex gap-4">
                        <div class="stat-item">
                          <span class="stat-number fw-bold text-primary">{{ userPosts.length }}</span>
                          <span class="stat-label text-muted">Posts</span>
                        </div>
                        <div class="stat-item">
                          <span class="stat-number fw-bold text-success">{{ savedPosts.length }}</span>
                          <span class="stat-label text-muted">Saved</span>
                        </div>
                        <div class="stat-item">
                          <span class="stat-number fw-bold text-info">{{ totalLikes }}</span>
                          <span class="stat-label text-muted">Likes Received</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-4 text-md-end">
                  <div class="profile-meta">
                    <small class="text-muted">
                      <i class="bi bi-calendar me-1"></i>
                      Member since {{ formatDate(profileUser.created_at) }}
                    </small>
                    <div class="mt-2">
                      <span class="badge bg-secondary">
                        <i class="bi bi-shield me-1"></i>{{ profileUser.role }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Content Tabs -->
        <div class="col-12">
          <div class="card shadow-sm">
            <!-- Tab Navigation -->
            <div class="card-header bg-white">
              <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button 
                    class="nav-link"
                    :class="{ active: activeTab === 'posts' }"
                    @click="activeTab = 'posts'"
                    type="button"
                  >
                    <i class="bi bi-journal-text me-2"></i>
                    My Posts ({{ userPosts.length }})
                  </button>
                </li>
                <li class="nav-item" role="presentation" v-if="isOwnProfile">
                  <button 
                    class="nav-link"
                    :class="{ active: activeTab === 'saved' }"
                    @click="activeTab = 'saved'"
                    type="button"
                  >
                    <i class="bi bi-bookmark-fill me-2"></i>
                    Saved Posts ({{ savedPosts.length }})
                  </button>
                </li>
                <li class="nav-item" role="presentation" v-if="isOwnProfile">
                  <button 
                    class="nav-link"
                    :class="{ active: activeTab === 'drafts' }"
                    @click="activeTab = 'drafts'"
                    type="button"
                  >
                    <i class="bi bi-file-earmark me-2"></i>
                    Drafts ({{ draftPosts.length }})
                  </button>
                </li>
              </ul>
            </div>

            <!-- Tab Content -->
            <div class="card-body">
              <!-- My Posts Tab -->
              <div v-if="activeTab === 'posts'" class="tab-content">
                <div v-if="postsLoading" class="text-center py-4">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading posts...</span>
                  </div>
                </div>
                
                <div v-else-if="userPosts.length === 0" class="text-center py-5 text-muted">
                  <i class="bi bi-journal display-4 mb-3"></i>
                  <h5>No Posts Yet</h5>
                  <p>{{ isOwnProfile ? "You haven't published any posts yet." : "This user hasn't published any posts yet." }}</p>
                  <router-link v-if="isOwnProfile" to="/create-post" class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i>Create Your First Post
                  </router-link>
                </div>
                
                <div v-else class="row">
                  <div v-for="post in userPosts" :key="post.id" class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 post-card">
                      <!-- Post Image -->
                      <div v-if="post.image" class="card-img-container">
                        <img :src="post.image" :alt="post.title" class="card-img-top">
                      </div>
                      
                      <div class="card-body d-flex flex-column">
                        <h6 class="card-title">
                          <router-link :to="`/posts/${post.id}`" class="text-decoration-none">
                            {{ post.title }}
                          </router-link>
                        </h6>
                        
                        <p class="card-text text-muted small flex-grow-1">
                          {{ truncateContent(post.content, 100) }}
                        </p>
                        
                        <div class="post-meta mt-auto">
                          <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                              <i class="bi bi-calendar me-1"></i>
                              {{ formatDate(post.created_at) }}
                            </small>
                            <div class="post-stats">
                              <small class="text-muted me-2">
                                <i class="bi bi-heart me-1"></i>{{ post.likes_count || 0 }}
                              </small>
                              <small class="text-muted">
                                <i class="bi bi-chat me-1"></i>{{ post.comments_count || 0 }}
                              </small>
                            </div>
                          </div>
                          
                          <!-- Post Actions for Own Posts -->
                          <div v-if="isOwnProfile" class="post-actions mt-2">
                            <div class="btn-group btn-group-sm w-100">
                              <router-link :to="`/posts/${post.id}`" class="btn btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>View
                              </router-link>
                              <router-link :to="`/posts/${post.id}/edit`" class="btn btn-outline-secondary">
                                <i class="bi bi-pencil me-1"></i>Edit
                              </router-link>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Saved Posts Tab -->
              <div v-if="activeTab === 'saved' && isOwnProfile" class="tab-content">
                <div v-if="savedLoading" class="text-center py-4">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading saved posts...</span>
                  </div>
                </div>
                
                <div v-else-if="savedPosts.length === 0" class="text-center py-5 text-muted">
                  <i class="bi bi-bookmark display-4 mb-3"></i>
                  <h5>No Saved Posts</h5>
                  <p>You haven't saved any posts yet. Save posts to read them later!</p>
                </div>
                
                <div v-else class="row">
                  <div v-for="post in savedPosts" :key="post.id" class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 post-card">
                      <!-- Post Image -->
                      <div v-if="post.image" class="card-img-container">
                        <img :src="post.image" :alt="post.title" class="card-img-top">
                      </div>
                      
                      <div class="card-body d-flex flex-column">
                        <h6 class="card-title">
                          <router-link :to="`/posts/${post.id}`" class="text-decoration-none">
                            {{ post.title }}
                          </router-link>
                        </h6>
                        
                        <p class="card-text text-muted small">
                          By {{ post.user.name }}
                        </p>
                        
                        <p class="card-text text-muted small flex-grow-1">
                          {{ truncateContent(post.content, 100) }}
                        </p>
                        
                        <div class="post-meta mt-auto">
                          <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                              <i class="bi bi-calendar me-1"></i>
                              {{ formatDate(post.created_at) }}
                            </small>
                            <div class="post-stats">
                              <small class="text-muted me-2">
                                <i class="bi bi-heart me-1"></i>{{ post.likes_count || 0 }}
                              </small>
                              <small class="text-muted">
                                <i class="bi bi-chat me-1"></i>{{ post.comments_count || 0 }}
                              </small>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Drafts Tab -->
              <div v-if="activeTab === 'drafts' && isOwnProfile" class="tab-content">
                <div v-if="draftsLoading" class="text-center py-4">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading drafts...</span>
                  </div>
                </div>
                
                <div v-else-if="draftPosts.length === 0" class="text-center py-5 text-muted">
                  <i class="bi bi-file-earmark display-4 mb-3"></i>
                  <h5>No Drafts</h5>
                  <p>You don't have any draft posts. Start writing to save drafts!</p>
                  <router-link to="/create-post" class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i>Create New Post
                  </router-link>
                </div>
                
                <div v-else class="row">
                  <div v-for="post in draftPosts" :key="post.id" class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 post-card draft-card">
                      <!-- Post Image -->
                      <div v-if="post.image" class="card-img-container">
                        <img :src="post.image" :alt="post.title" class="card-img-top">
                      </div>
                      
                      <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                          <h6 class="card-title mb-0">{{ post.title }}</h6>
                          <span class="badge bg-warning text-dark">Draft</span>
                        </div>
                        
                        <p class="card-text text-muted small flex-grow-1">
                          {{ truncateContent(post.content, 100) }}
                        </p>
                        
                        <div class="post-meta mt-auto">
                          <small class="text-muted mb-2 d-block">
                            <i class="bi bi-clock me-1"></i>
                            Last updated {{ formatDate(post.updated_at) }}
                          </small>
                          
                          <div class="post-actions">
                            <div class="btn-group btn-group-sm w-100">
                              <router-link :to="`/posts/${post.id}/edit`" class="btn btn-outline-primary">
                                <i class="bi bi-pencil me-1"></i>Continue Writing
                              </router-link>
                              <router-link :to="`/posts/${post.id}`" class="btn btn-outline-secondary">
                                <i class="bi bi-eye me-1"></i>Preview
                              </router-link>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
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
  name: 'ProfileView',
  props: {
    id: {
      type: [String, Number],
      default: null
    }
  },
  setup(props) {
    const authStore = useAuthStore()
    const postsStore = usePostsStore()
    const route = useRoute()
    const router = useRouter()
    
    const loading = ref(true)
    const postsLoading = ref(false)
    const savedLoading = ref(false)
    const draftsLoading = ref(false)
    const error = ref(null)
    
    const profileUser = ref(null)
    const userPosts = ref([])
    const savedPosts = ref([])
    const draftPosts = ref([])
    const activeTab = ref('posts')
    
    // Computed
    const isOwnProfile = computed(() => {
      return authStore.isAuthenticated && 
             (!props.id || props.id == authStore.user?.id)
    })
    
    const totalLikes = computed(() => {
      return userPosts.value.reduce((total, post) => total + (post.likes_count || 0), 0)
    })
    
    // Methods
    const fetchProfile = async () => {
      try {
        loading.value = true
        error.value = null
        
        const userId = props.id || authStore.user?.id
        
        if (!userId) {
          error.value = 'User not found'
          return
        }
        
        // For now, if it's own profile, use current user data
        // In a real app, you'd fetch user data from API
        if (isOwnProfile.value) {
          profileUser.value = authStore.user
        } else {
          // TODO: Implement API call to fetch other user's profile
          // const response = await axios.get(`/users/${userId}`)
          // profileUser.value = response.data
          error.value = 'Viewing other user profiles not implemented yet'
          return
        }
        
        // Fetch user's posts
        await fetchUserPosts()
        
        // Fetch saved posts if own profile
        if (isOwnProfile.value) {
          await fetchSavedPosts()
          await fetchDraftPosts()
        }
        
      } catch (err) {
        console.error('Error fetching profile:', err)
        error.value = err.response?.data?.message || 'Failed to load profile'
      } finally {
        loading.value = false
      }
    }
    
    const fetchUserPosts = async () => {
      try {
        postsLoading.value = true
        
        if (isOwnProfile.value) {
          // Fetch own posts (including drafts, then filter)
          const response = await postsStore.fetchMyPosts()
          userPosts.value = response.data.filter(post => post.status === 'published')
        } else {
          // TODO: Implement API call to fetch other user's published posts
          // const response = await axios.get(`/users/${props.id}/posts`)
          // userPosts.value = response.data
          userPosts.value = []
        }
        
      } catch (err) {
        console.error('Error fetching user posts:', err)
      } finally {
        postsLoading.value = false
      }
    }
    
    const fetchSavedPosts = async () => {
      try {
        savedLoading.value = true
        const response = await postsStore.fetchSavedPosts()
        savedPosts.value = response.data || []
      } catch (err) {
        console.error('Error fetching saved posts:', err)
        savedPosts.value = []
      } finally {
        savedLoading.value = false
      }
    }
    
    const fetchDraftPosts = async () => {
      try {
        draftsLoading.value = true
        const response = await postsStore.fetchMyPosts()
        draftPosts.value = response.data.filter(post => post.status === 'draft')
      } catch (err) {
        console.error('Error fetching draft posts:', err)
        draftPosts.value = []
      } finally {
        draftsLoading.value = false
      }
    }
    
    const truncateContent = (content, maxLength) => {
      if (!content) return ''
      const plainText = content.replace(/<[^>]*>/g, '').replace(/\*\*|__|\*/g, '')
      return plainText.length > maxLength 
        ? plainText.substring(0, maxLength) + '...'
        : plainText
    }
    
    const formatDate = (dateString) => {
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }
    
    // Watch for route changes
    watch(() => route.params.id, () => {
      fetchProfile()
    })
    
    // Lifecycle
    onMounted(() => {
      if (!authStore.isAuthenticated) {
        router.push('/login')
        return
      }
      fetchProfile()
    })
    
    return {
      loading,
      postsLoading,
      savedLoading,
      draftsLoading,
      error,
      profileUser,
      userPosts,
      savedPosts,
      draftPosts,
      activeTab,
      isOwnProfile,
      totalLikes,
      truncateContent,
      formatDate
    }
  }
}
</script>

<style scoped>
.profile-page {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.profile-avatar {
  flex-shrink: 0;
}

.avatar-circle {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  font-size: 2rem;
}

.profile-name {
  font-size: 2rem;
  font-weight: 700;
  color: #212529;
  margin-bottom: 0.5rem;
}

.profile-stats {
  display: flex;
  gap: 2rem;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.stat-number {
  font-size: 1.5rem;
  line-height: 1;
}

.stat-label {
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.nav-tabs .nav-link {
  border: none;
  color: #6c757d;
  font-weight: 500;
}

.nav-tabs .nav-link.active {
  color: #0d6efd;
  border-bottom: 2px solid #0d6efd;
  background: none;
}

.nav-tabs .nav-link:hover {
  color: #0d6efd;
  border-color: transparent;
}

.post-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border: none;
  border-radius: 12px;
}

.post-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.card-img-container {
  height: 200px;
  overflow: hidden;
  border-radius: 12px 12px 0 0;
}

.card-img-top {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.draft-card {
  border-left: 4px solid #ffc107;
}

.post-actions .btn-group {
  box-shadow: none;
}

.post-actions .btn {
  font-size: 0.875rem;
  padding: 0.375rem 0.75rem;
}

@media (max-width: 768px) {
  .profile-name {
    font-size: 1.5rem;
  }
  
  .avatar-circle {
    width: 60px;
    height: 60px;
    font-size: 1.5rem;
  }
  
  .profile-stats {
    gap: 1rem;
  }
  
  .stat-number {
    font-size: 1.25rem;
  }
  
  .nav-tabs {
    flex-wrap: wrap;
  }
  
  .nav-tabs .nav-link {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
  }
}

@media (max-width: 576px) {
  .profile-stats {
    justify-content: space-around;
    width: 100%;
    margin-top: 1rem;
  }
  
  .d-flex.align-items-center {
    flex-direction: column;
    align-items: flex-start !important;
  }
  
  .profile-avatar {
    margin-bottom: 1rem;
  }
}
</style>
