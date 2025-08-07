<template>
  <div class="edit-post-page">
    <div class="container py-5">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading post...</span>
        </div>
        <p class="mt-3 text-muted">Loading post data...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ error }}
      </div>

      <!-- Edit Post Form -->
      <div v-else-if="post" class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
              <h2 class="h4 mb-0">
                <i class="bi bi-pencil-square me-2"></i>
                Edit Post
              </h2>
            </div>
            
            <div class="card-body">
              <form @submit.prevent="updatePost">
                <!-- Title -->
                <div class="mb-4">
                  <label for="title" class="form-label fw-semibold">
                    Post Title <span class="text-danger">*</span>
                  </label>
                  <input
                    type="text"
                    id="title"
                    class="form-control form-control-lg"
                    :class="{ 'is-invalid': errors.title }"
                    v-model="form.title"
                    placeholder="Enter your post title..."
                    required
                  >
                  <div v-if="errors.title" class="invalid-feedback">
                    {{ errors.title[0] }}
                  </div>
                </div>

                <!-- Content -->
                <div class="mb-4">
                  <label for="content" class="form-label fw-semibold">
                    Content <span class="text-danger">*</span>
                  </label>
                  
                  <!-- Formatting Toolbar -->
                  <div class="formatting-toolbar border rounded-top p-2 bg-light">
                    <div class="btn-group btn-group-sm" role="group">
                      <button
                        type="button"
                        class="btn btn-outline-secondary"
                        @click="formatText('bold')"
                        title="Bold"
                      >
                        <i class="bi bi-type-bold"></i>
                      </button>
                      <button
                        type="button"
                        class="btn btn-outline-secondary"
                        @click="formatText('italic')"
                        title="Italic"
                      >
                        <i class="bi bi-type-italic"></i>
                      </button>
                      <button
                        type="button"
                        class="btn btn-outline-secondary"
                        @click="formatText('underline')"
                        title="Underline"
                      >
                        <i class="bi bi-type-underline"></i>
                      </button>
                    </div>
                    <div class="btn-group btn-group-sm ms-2" role="group">
                      <button
                        type="button"
                        class="btn btn-outline-secondary"
                        @click="formatText('unorderedList')"
                        title="Bullet List"
                      >
                        <i class="bi bi-list-ul"></i>
                      </button>
                      <button
                        type="button"
                        class="btn btn-outline-secondary"
                        @click="formatText('orderedList')"
                        title="Numbered List"
                      >
                        <i class="bi bi-list-ol"></i>
                      </button>
                    </div>
                    <button
                      type="button"
                      class="btn btn-outline-info btn-sm ms-2"
                      @click="showPreview = !showPreview"
                    >
                      <i class="bi bi-eye me-1"></i>
                      {{ showPreview ? 'Hide Preview' : 'Preview' }}
                    </button>
                  </div>

                  <textarea
                    id="content"
                    ref="contentTextarea"
                    class="form-control"
                    :class="{ 'is-invalid': errors.content }"
                    v-model="form.content"
                    placeholder="Write your post content here..."
                    rows="12"
                    style="border-top: none; border-top-left-radius: 0; border-top-right-radius: 0;"
                    required
                  ></textarea>
                  
                  <div v-if="errors.content" class="invalid-feedback">
                    {{ errors.content[0] }}
                  </div>
                  
                  <div class="form-text">
                    Use the toolbar above for basic formatting. Supports bold, italic, underline, and lists.
                  </div>
                </div>

                <!-- Current Featured Image -->
                <div v-if="post.image" class="mb-4">
                  <label class="form-label fw-semibold">Current Featured Image</label>
                  <div class="current-image-container">
                    <img :src="post.image" :alt="post.title" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                    <div class="mt-2">
                      <small class="text-muted">Current image will be replaced if you upload a new one</small>
                    </div>
                  </div>
                </div>

                <!-- New Featured Image Upload -->
                <div class="mb-4">
                  <label for="image" class="form-label fw-semibold">
                    {{ post.image ? 'Replace Featured Image' : 'Add Featured Image' }}
                  </label>
                  <input
                    type="file"
                    id="image"
                    class="form-control"
                    :class="{ 'is-invalid': errors.image }"
                    @change="handleImageUpload"
                    accept="image/*"
                  >
                  <div v-if="errors.image" class="invalid-feedback">
                    {{ errors.image[0] }}
                  </div>
                  <div class="form-text">
                    Supported formats: JPG, PNG, GIF. Max size: 5MB.
                  </div>
                  
                  <!-- Image Preview -->
                  <div v-if="imagePreview" class="mt-3">
                    <img :src="imagePreview" alt="New image preview" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                    <div class="mt-2">
                      <button type="button" class="btn btn-outline-danger btn-sm" @click="removeNewImage">
                        <i class="bi bi-trash me-1"></i>Remove New Image
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Status -->
                <div class="mb-4">
                  <label for="status" class="form-label fw-semibold">Status</label>
                  <select
                    id="status"
                    class="form-select"
                    :class="{ 'is-invalid': errors.status }"
                    v-model="form.status"
                  >
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                  </select>
                  <div v-if="errors.status" class="invalid-feedback">
                    {{ errors.status[0] }}
                  </div>
                  <div class="form-text">
                    Drafts are only visible to you. Published posts are visible to everyone.
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between align-items-center">
                  <button
                    type="button"
                    class="btn btn-outline-secondary"
                    @click="goBack"
                  >
                    <i class="bi bi-arrow-left me-1"></i>
                    Cancel
                  </button>
                  
                  <div class="d-flex gap-2">
                    <button
                      type="button"
                      class="btn btn-outline-info"
                      @click="showPreviewModal = true"
                      :disabled="!form.title.trim() || !form.content.trim()"
                    >
                      <i class="bi bi-eye me-1"></i>
                      Preview
                    </button>
                    
                    <button
                      type="submit"
                      class="btn btn-primary"
                      :disabled="submitting || !form.title.trim() || !form.content.trim()"
                    >
                      <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                      <i v-else class="bi bi-check-lg me-1"></i>
                      {{ submitting ? 'Updating...' : 'Update Post' }}
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" ref="previewModal">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Post Preview</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <article class="preview-content">
              <!-- Preview Image -->
              <div v-if="imagePreview || post.image" class="mb-4">
                <img 
                  :src="imagePreview || post.image" 
                  :alt="form.title" 
                  class="img-fluid rounded shadow-sm w-100"
                  style="max-height: 300px; object-fit: cover;"
                >
              </div>
              
              <!-- Preview Title -->
              <h1 class="preview-title mb-3">{{ form.title }}</h1>
              
              <!-- Preview Meta -->
              <div class="preview-meta mb-4 text-muted">
                <small>
                  <i class="bi bi-person-circle me-1"></i>
                  {{ authStore.user?.name }}
                  <span class="mx-2">•</span>
                  <i class="bi bi-calendar me-1"></i>
                  {{ new Date().toLocaleDateString() }}
                  <span class="mx-2">•</span>
                  <span class="badge" :class="form.status === 'published' ? 'bg-success' : 'bg-warning'">
                    {{ form.status }}
                  </span>
                </small>
              </div>
              
              <!-- Preview Content -->
              <div class="preview-content-body" v-html="formattedPreviewContent"></div>
            </article>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close Preview</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth'
import { usePostsStore } from '@/stores/posts'
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export default {
  name: 'EditPostView',
  props: {
    id: {
      type: [String, Number],
      required: true
    }
  },
  setup(props) {
    const authStore = useAuthStore()
    const postsStore = usePostsStore()
    const route = useRoute()
    const router = useRouter()
    
    const loading = ref(true)
    const submitting = ref(false)
    const error = ref(null)
    const post = ref(null)
    const showPreview = ref(false)
    const showPreviewModal = ref(false)
    const imagePreview = ref(null)
    
    const form = reactive({
      title: '',
      content: '',
      image: null,
      status: 'draft'
    })
    
    const errors = ref({})
    
    // Computed
    const formattedPreviewContent = computed(() => {
      if (!form.content) return ''
      
      return form.content
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/__(.*?)__/g, '<u>$1</u>')
        .replace(/\n/g, '<br>')
    })
    
    // Methods
    const fetchPost = async () => {
      try {
        loading.value = true
        error.value = null
        
        const postData = await postsStore.getPost(props.id)
        post.value = postData
        
        // Check if user can edit this post
        if (!canEditPost(postData)) {
          error.value = 'You do not have permission to edit this post.'
          return
        }
        
        // Populate form with existing data
        form.title = postData.title
        form.content = postData.content
        form.status = postData.status
        
      } catch (err) {
        console.error('Error fetching post:', err)
        error.value = err.response?.data?.message || 'Failed to load post'
      } finally {
        loading.value = false
      }
    }
    
    const canEditPost = (postData) => {
      return authStore.isAuthenticated && 
             (authStore.user?.id === postData.user_id || authStore.isAdmin)
    }
    
    const handleImageUpload = (event) => {
      const file = event.target.files[0]
      if (file) {
        // Validate file size (5MB limit)
        if (file.size > 5 * 1024 * 1024) {
          errors.value.image = ['Image size must be less than 5MB']
          return
        }
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
          errors.value.image = ['Please select a valid image file']
          return
        }
        
        form.image = file
        
        // Create preview
        const reader = new FileReader()
        reader.onload = (e) => {
          imagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
        
        // Clear any previous errors
        if (errors.value.image) {
          delete errors.value.image
        }
      }
    }
    
    const removeNewImage = () => {
      form.image = null
      imagePreview.value = null
      document.getElementById('image').value = ''
    }
    
    const formatText = (command) => {
      const textarea = document.getElementById('content')
      const start = textarea.selectionStart
      const end = textarea.selectionEnd
      const selectedText = textarea.value.substring(start, end)
      
      let formattedText = ''
      
      switch (command) {
        case 'bold':
          formattedText = `**${selectedText}**`
          break
        case 'italic':
          formattedText = `*${selectedText}*`
          break
        case 'underline':
          formattedText = `__${selectedText}__`
          break
        case 'unorderedList':
          formattedText = selectedText.split('\n').map(line => `• ${line}`).join('\n')
          break
        case 'orderedList':
          formattedText = selectedText.split('\n').map((line, index) => `${index + 1}. ${line}`).join('\n')
          break
      }
      
      const newContent = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end)
      form.content = newContent
      
      // Focus back to textarea
      textarea.focus()
      textarea.setSelectionRange(start + formattedText.length, start + formattedText.length)
    }
    
    const updatePost = async () => {
      try {
        submitting.value = true
        errors.value = {}
        
        const postData = {
          title: form.title.trim(),
          content: form.content.trim(),
          status: form.status
        }
        
        // Only include image if a new one was selected
        if (form.image) {
          postData.image = form.image
        }
        
        const response = await postsStore.updatePost(props.id, postData)
        
        // Redirect to the updated post
        router.push(`/posts/${props.id}`)
        
      } catch (err) {
        console.error('Error updating post:', err)
        if (err.response?.data?.errors) {
          errors.value = err.response.data.errors
        } else {
          errors.value = { general: [err.response?.data?.message || 'Failed to update post'] }
        }
      } finally {
        submitting.value = false
      }
    }
    
    const goBack = () => {
      router.push(`/posts/${props.id}`)
    }
    
    // Watch for preview modal
    watch(showPreviewModal, (show) => {
      if (show) {
        const modal = new bootstrap.Modal(document.getElementById('previewModal'))
        modal.show()
      }
    })
    
    // Lifecycle
    onMounted(() => {
      fetchPost()
    })
    
    return {
      authStore,
      loading,
      submitting,
      error,
      post,
      form,
      errors,
      showPreview,
      showPreviewModal,
      imagePreview,
      formattedPreviewContent,
      handleImageUpload,
      removeNewImage,
      formatText,
      updatePost,
      goBack
    }
  }
}
</script>

<style scoped>
.edit-post-page {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.formatting-toolbar {
  border-bottom: none !important;
}

.formatting-toolbar .btn {
  border-color: #dee2e6;
}

.formatting-toolbar .btn:hover {
  background-color: #e9ecef;
}

.preview-title {
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1.2;
  color: #212529;
}

.preview-content-body {
  font-size: 1.1rem;
  line-height: 1.8;
  color: #495057;
  font-family: Georgia, serif;
}

.preview-content-body p {
  margin-bottom: 1.5rem;
}

.current-image-container img {
  max-width: 100%;
  height: auto;
}

@media (max-width: 768px) {
  .preview-title {
    font-size: 2rem;
  }
  
  .formatting-toolbar {
    flex-wrap: wrap;
  }
  
  .formatting-toolbar .btn-group {
    margin-bottom: 0.5rem;
  }
}
</style>
