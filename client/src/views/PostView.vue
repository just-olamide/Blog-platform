<template>
  <div class="post-view-page">
    <div class="container py-4">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted">Loading post...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="row justify-content-center">
        <div class="col-md-8">
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ error }}
          </div>
          <div class="text-center">
            <router-link to="/" class="btn btn-primary">
              <i class="bi bi-arrow-left me-1"></i>Back to Home
            </router-link>
          </div>
        </div>
      </div>

      <!-- Post Content -->
      <div v-else-if="post" class="row justify-content-center">
        <div class="col-lg-8">
          <!-- Back Button -->
          <div class="mb-3">
            <router-link to="/" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-left me-1"></i>Back to Posts
            </router-link>
          </div>

          <!-- Main Post Card -->
          <article class="card shadow-sm mb-4">
            <!-- Featured Image -->
            <div v-if="post.image" class="card-img-top-container">
              <img :src="post.image" :alt="post.title" class="card-img-top post-featured-image">
            </div>

            <div class="card-body">
              <!-- Post Header -->
              <div class="post-header mb-4">
                <h1 class="post-title mb-3">{{ post.title }}</h1>
                
                <!-- Post Meta -->
                <div class="post-meta d-flex align-items-center justify-content-between flex-wrap mb-3">
                  <div class="author-info d-flex align-items-center">
                    <div class="author-avatar me-2">
                      <i class="bi bi-person-circle fs-4 text-primary"></i>
                    </div>
                    <div>
                      <div class="author-name fw-semibold">{{ post.user.name }}</div>
                      <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        {{ formatDate(post.created_at) }}
                        <span v-if="post.updated_at !== post.created_at" class="ms-2">
                          <i class="bi bi-pencil me-1"></i>Updated {{ formatDate(post.updated_at) }}
                        </span>
                      </small>
                    </div>
                  </div>
                  
                  <!-- Post Status & Actions -->
                  <div class="post-actions d-flex align-items-center">
                    <span class="badge bg-success me-2" v-if="post.status === 'published'">
                      <i class="bi bi-globe me-1"></i>Published
                    </span>
                    <span class="badge bg-secondary me-2" v-else>
                      <i class="bi bi-file-earmark me-1"></i>Draft
                    </span>
                    
                    <!-- Edit/Delete for Post Owner -->
                    <div v-if="canEditPost" class="dropdown">
                      <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li>
                          <router-link :to="`/posts/${post.id}/edit`" class="dropdown-item">
                            <i class="bi bi-pencil me-2"></i>Edit Post
                          </router-link>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                          <button class="dropdown-item text-danger" @click="confirmDeletePost">
                            <i class="bi bi-trash me-2"></i>Delete Post
                          </button>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Post Content -->
              <div class="post-content mb-4" v-html="formattedContent"></div>

              <!-- Post Interactions -->
              <div class="post-interactions border-top pt-3">
                <div class="row">
                  <div class="col-md-8">
                    <div class="interaction-buttons d-flex align-items-center gap-3">
                      <!-- Like Button -->
                      <button 
                        class="btn btn-outline-danger btn-sm d-flex align-items-center"
                        :class="{ 'btn-danger text-white': post.is_liked }"
                        @click="toggleLike"
                        :disabled="interactionLoading"
                      >
                        <i class="bi bi-heart-fill me-1" v-if="post.is_liked"></i>
                        <i class="bi bi-heart me-1" v-else></i>
                        {{ post.likes_count }} {{ post.likes_count === 1 ? 'Like' : 'Likes' }}
                      </button>

                      <!-- Save Button -->
                      <button 
                        class="btn btn-outline-warning btn-sm d-flex align-items-center"
                        :class="{ 'btn-warning text-white': post.is_saved }"
                        @click="toggleSave"
                        :disabled="interactionLoading"
                      >
                        <i class="bi bi-bookmark-fill me-1" v-if="post.is_saved"></i>
                        <i class="bi bi-bookmark me-1" v-else></i>
                        {{ post.is_saved ? 'Saved' : 'Save' }}
                      </button>

                      <!-- Repost Button -->
                      <button 
                        class="btn btn-outline-info btn-sm d-flex align-items-center"
                        :class="{ 'btn-info text-white': post.is_reposted }"
                        @click="toggleRepost"
                        :disabled="interactionLoading"
                      >
                        <i class="bi bi-arrow-repeat me-1"></i>
                        {{ post.reposts_count }} {{ post.reposts_count === 1 ? 'Repost' : 'Reposts' }}
                      </button>

                      <!-- Comments Count -->
                      <span class="text-muted d-flex align-items-center">
                        <i class="bi bi-chat me-1"></i>
                        {{ post.comments_count }} {{ post.comments_count === 1 ? 'Comment' : 'Comments' }}
                      </span>
                    </div>
                  </div>
                  <div class="col-md-4 text-md-end">
                    <small class="text-muted">
                      <i class="bi bi-eye me-1"></i>{{ post.views_count || 0 }} views
                    </small>
                  </div>
                </div>
              </div>
            </div>
          </article>

          <!-- Comments Section -->
          <div class="comments-section">
            <div class="comments-header d-flex align-items-center justify-content-between mb-4">
              <h3 class="h5 mb-0">
                <i class="bi bi-chat-dots me-2"></i>
                Comments ({{ comments.length }})
              </h3>
              <button 
                class="btn btn-outline-primary btn-sm"
                @click="showCommentForm = !showCommentForm"
                v-if="authStore.isAuthenticated"
              >
                <i class="bi bi-plus me-1"></i>Add Comment
              </button>
            </div>

            <!-- Add Comment Form -->
            <div v-if="showCommentForm && authStore.isAuthenticated" class="card mb-4">
              <div class="card-body">
                <form @submit.prevent="submitComment">
                  <div class="mb-3">
                    <label for="comment-content" class="form-label">Your Comment</label>
                    <textarea
                      id="comment-content"
                      class="form-control"
                      :class="{ 'is-invalid': commentErrors.content }"
                      v-model="newComment.content"
                      placeholder="Share your thoughts..."
                      rows="4"
                      required
                    ></textarea>
                    <div v-if="commentErrors.content" class="invalid-feedback">
                      {{ commentErrors.content[0] }}
                    </div>
                  </div>
                  <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" @click="cancelComment">
                      Cancel
                    </button>
                    <button 
                      type="submit" 
                      class="btn btn-primary"
                      :disabled="commentLoading || !newComment.content.trim()"
                    >
                      <span v-if="commentLoading" class="spinner-border spinner-border-sm me-2"></span>
                      <i v-else class="bi bi-send me-1"></i>
                      Post Comment
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Login Prompt for Guests -->
            <div v-if="!authStore.isAuthenticated" class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>
              <router-link to="/login">Login</router-link> or 
              <router-link to="/register">register</router-link> to join the conversation.
            </div>

            <!-- Comments List -->
            <div class="comments-list">
              <div v-if="commentsLoading" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                  <span class="visually-hidden">Loading comments...</span>
                </div>
              </div>

              <div v-else-if="comments.length === 0" class="text-center py-5 text-muted">
                <i class="bi bi-chat display-4 mb-3"></i>
                <p>No comments yet. Be the first to share your thoughts!</p>
              </div>

              <div v-else>
                <div 
                  v-for="comment in comments" 
                  :key="comment.id" 
                  class="comment-item card mb-3"
                >
                  <div class="card-body">
                    <!-- Comment Header -->
                    <div class="comment-header d-flex align-items-center justify-content-between mb-2">
                      <div class="comment-author d-flex align-items-center">
                        <i class="bi bi-person-circle fs-5 text-primary me-2"></i>
                        <div>
                          <div class="author-name fw-semibold">{{ comment.user.name }}</div>
                          <small class="text-muted">{{ formatDate(comment.created_at) }}</small>
                        </div>
                      </div>
                      
                      <!-- Comment Actions -->
                      <div v-if="canEditComment(comment)" class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                          <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu">
                          <li>
                            <button class="dropdown-item" @click="editComment(comment)">
                              <i class="bi bi-pencil me-2"></i>Edit
                            </button>
                          </li>
                          <li><hr class="dropdown-divider"></li>
                          <li>
                            <button class="dropdown-item text-danger" @click="confirmDeleteComment(comment)">
                              <i class="bi bi-trash me-2"></i>Delete
                            </button>
                          </li>
                        </ul>
                      </div>
                    </div>

                    <!-- Comment Content -->
                    <div v-if="editingComment?.id !== comment.id" class="comment-content">
                      <p class="mb-0" v-html="formatCommentContent(comment.content)"></p>
                    </div>

                    <!-- Edit Comment Form -->
                    <div v-else class="edit-comment-form">
                      <form @submit.prevent="updateComment">
                        <div class="mb-3">
                          <textarea
                            class="form-control"
                            v-model="editingComment.content"
                            rows="3"
                            required
                          ></textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                          <button type="button" class="btn btn-outline-secondary btn-sm" @click="cancelEditComment">
                            Cancel
                          </button>
                          <button 
                            type="submit" 
                            class="btn btn-primary btn-sm"
                            :disabled="commentLoading"
                          >
                            <span v-if="commentLoading" class="spinner-border spinner-border-sm me-2"></span>
                            Update
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modals -->
    <!-- Delete Post Modal -->
    <div class="modal fade" id="deletePostModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete Post</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete this post? This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="deletePost" :disabled="deleteLoading">
              <span v-if="deleteLoading" class="spinner-border spinner-border-sm me-2"></span>
              Delete Post
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Comment Modal -->
    <div class="modal fade" id="deleteCommentModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete Comment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete this comment? This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="deleteComment" :disabled="deleteLoading">
              <span v-if="deleteLoading" class="spinner-border spinner-border-sm me-2"></span>
              Delete Comment
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth'
import { usePostsStore } from '@/stores/posts'
import { ref, computed, onMounted, reactive, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export default {
  name: 'PostView',
  setup() {
    const authStore = useAuthStore()
    const postsStore = usePostsStore()
    const route = useRoute()
    const router = useRouter()
    
    const loading = ref(true)
    const error = ref(null)
    const post = ref(null)
    const comments = ref([])
    const commentsLoading = ref(false)
    const interactionLoading = ref(false)
    const commentLoading = ref(false)
    const deleteLoading = ref(false)
    
    const showCommentForm = ref(false)
    const newComment = reactive({
      content: ''
    })
    const commentErrors = ref({})
    
    const editingComment = ref(null)
    const commentToDelete = ref(null)
    
    const canEditPost = computed(() => {
      return authStore.isAuthenticated && 
             (authStore.user?.id === post.value?.user_id || authStore.isAdmin)
    })
    
    const canEditComment = (comment) => {
      return authStore.isAuthenticated && 
             (authStore.user?.id === comment.user_id || authStore.isAdmin)
    }
    
    const formattedContent = computed(() => {
      if (!post.value?.content) return ''
      return post.value.content
        .replace(/\n/g, '<br>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/__(.*?)__/g, '<u>$1</u>')
    })
    
    const formatCommentContent = (content) => {
      return content.replace(/\n/g, '<br>')
    }
    
    const formatDate = (dateString) => {
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
    
    const fetchPost = async () => {
      try {
        loading.value = true
        error.value = null
        
        const postId = route.params.id
        const response = await postsStore.getPost(postId)
        post.value = response
        
        // Fetch comments
        await fetchComments()
        
      } catch (err) {
        console.error('Error fetching post:', err)
        error.value = err.response?.data?.message || 'Post not found'
      } finally {
        loading.value = false
      }
    }
    
    const fetchComments = async () => {
      try {
        commentsLoading.value = true
        const postId = route.params.id
        comments.value = await postsStore.getPostComments(postId)
      } catch (err) {
        console.error('Error fetching comments:', err)
      } finally {
        commentsLoading.value = false
      }
    }
    
    const toggleLike = async () => {
      if (!authStore.isAuthenticated) {
        router.push('/login')
        return
      }
      
      try {
        interactionLoading.value = true
        const response = await postsStore.toggleLike(post.value.id)
        
        // Update local state with API response data
        post.value.is_liked = response.liked
        post.value.likes_count = response.likes_count
        
      } catch (err) {
        console.error('Error toggling like:', err)
      } finally {
        interactionLoading.value = false
      }
    }
    
    const toggleSave = async () => {
      if (!authStore.isAuthenticated) {
        router.push('/login')
        return
      }
      
      try {
        interactionLoading.value = true
        const response = await postsStore.toggleSave(post.value.id)
        
        // Update local state with API response data
        post.value.is_saved = response.saved
        
      } catch (err) {
        console.error('Error toggling save:', err)
      } finally {
        interactionLoading.value = false
      }
    }
    
    const toggleRepost = async () => {
      if (!authStore.isAuthenticated) {
        router.push('/login')
        return
      }
      
      try {
        interactionLoading.value = true
        const response = await postsStore.toggleRepost(post.value.id)
        
        // Update local state with API response data
        post.value.is_reposted = response.reposted
        post.value.reposts_count = response.reposts_count
        
      } catch (err) {
        console.error('Error toggling repost:', err)
      } finally {
        interactionLoading.value = false
      }
    }
    
    const submitComment = async () => {
      if (!newComment.content.trim()) return
      
      try {
        commentLoading.value = true
        commentErrors.value = {}
        
        const comment = await postsStore.addComment(post.value.id, newComment.content.trim())
        
        // Add comment to local list
        comments.value.unshift(comment)
        post.value.comments_count++
        
        // Reset form
        newComment.content = ''
        showCommentForm.value = false
        
      } catch (err) {
        console.error('Error adding comment:', err)
        if (err.response?.data?.errors) {
          commentErrors.value = err.response.data.errors
        }
      } finally {
        commentLoading.value = false
      }
    }
    
    const cancelComment = () => {
      newComment.content = ''
      commentErrors.value = {}
      showCommentForm.value = false
    }
    
    const editComment = (comment) => {
      editingComment.value = { ...comment }
    }
    
    const cancelEditComment = () => {
      editingComment.value = null
    }
    
    const updateComment = async () => {
      try {
        commentLoading.value = true
        
        const updatedComment = await postsStore.updateComment(editingComment.value.id, {
          content: editingComment.value.content
        })
        
        // Update comment in local list
        const index = comments.value.findIndex(c => c.id === updatedComment.id)
        if (index !== -1) {
          comments.value[index] = updatedComment
        }
        
        editingComment.value = null
        
      } catch (err) {
        console.error('Error updating comment:', err)
      } finally {
        commentLoading.value = false
      }
    }
    
    const confirmDeletePost = () => {
      const modal = new bootstrap.Modal(document.getElementById('deletePostModal'))
      modal.show()
    }
    
    const deletePost = async () => {
      try {
        deleteLoading.value = true
        await postsStore.deletePost(post.value.id)
        
        // Close modal and redirect
        const modal = bootstrap.Modal.getInstance(document.getElementById('deletePostModal'))
        modal.hide()
        
        router.push('/')
        
      } catch (err) {
        console.error('Error deleting post:', err)
      } finally {
        deleteLoading.value = false
      }
    }
    
    const confirmDeleteComment = (comment) => {
      commentToDelete.value = comment
      const modal = new bootstrap.Modal(document.getElementById('deleteCommentModal'))
      modal.show()
    }
    
    const deleteComment = async () => {
      try {
        deleteLoading.value = true
        await postsStore.deleteComment(commentToDelete.value.id)
        
        // Remove comment from local list
        comments.value = comments.value.filter(c => c.id !== commentToDelete.value.id)
        post.value.comments_count--
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteCommentModal'))
        modal.hide()
        
        commentToDelete.value = null
        
      } catch (err) {
        console.error('Error deleting comment:', err)
      } finally {
        deleteLoading.value = false
      }
    }
    
    // Watch for route changes
    watch(() => route.params.id, fetchPost, { immediate: true })
    
    onMounted(() => {
      fetchPost()
    })
    
    return {
      authStore,
      loading,
      error,
      post,
      comments,
      commentsLoading,
      interactionLoading,
      commentLoading,
      deleteLoading,
      showCommentForm,
      newComment,
      commentErrors,
      editingComment,
      canEditPost,
      canEditComment,
      formattedContent,
      formatCommentContent,
      formatDate,
      toggleLike,
      toggleSave,
      toggleRepost,
      submitComment,
      cancelComment,
      editComment,
      cancelEditComment,
      updateComment,
      confirmDeletePost,
      deletePost,
      confirmDeleteComment,
      deleteComment
    }
  }
}
</script>

<style scoped>
.post-view-page {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.post-featured-image {
  height: 300px;
  object-fit: cover;
  width: 100%;
}

.post-title {
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1.2;
  color: #212529;
}

.post-content {
  font-size: 1.1rem;
  line-height: 1.8;
  color: #495057;
  font-family: Georgia, serif;
}

.post-content p {
  margin-bottom: 1.5rem;
}

.interaction-buttons .btn {
  transition: all 0.2s ease;
}

.interaction-buttons .btn:hover {
  transform: translateY(-1px);
}

.comment-item {
  border-left: 3px solid #e9ecef;
  transition: border-color 0.2s ease;
}

.comment-item:hover {
  border-left-color: #0d6efd;
}

.author-avatar {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card {
  border: none;
  border-radius: 12px;
}

.card-img-top-container {
  border-radius: 12px 12px 0 0;
  overflow: hidden;
}

@media (max-width: 768px) {
  .post-title {
    font-size: 2rem;
  }
  
  .interaction-buttons {
    flex-wrap: wrap;
    gap: 0.5rem !important;
  }
  
  .interaction-buttons .btn {
    font-size: 0.875rem;
  }
}
</style>
