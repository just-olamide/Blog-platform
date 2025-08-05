<template>
  <div class="create-post-page">
    <div class="container py-4">
      <!-- Header -->
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
              <h1 class="h3 mb-1">
                <i class="bi bi-pencil-square me-2 text-primary"></i>
                Create New Post
              </h1>
              <p class="text-muted mb-0">Share your thoughts with the world</p>
            </div>
            <router-link to="/" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i>Back to Home
            </router-link>
          </div>
        </div>
      </div>

      <!-- Main Form -->
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <form @submit.prevent="handleSubmit">
                <!-- Title Input -->
                <div class="mb-4">
                  <label for="title" class="form-label fw-semibold">
                    <i class="bi bi-type me-1"></i>Post Title *
                  </label>
                  <input
                    type="text"
                    id="title"
                    class="form-control form-control-lg"
                    :class="{ 'is-invalid': errors.title }"
                    v-model="form.title"
                    placeholder="Enter an engaging title for your post..."
                    maxlength="255"
                    required
                  >
                  <div class="form-text">
                    {{ form.title.length }}/255 characters
                  </div>
                  <div v-if="errors.title" class="invalid-feedback">
                    {{ errors.title[0] }}
                  </div>
                </div>

                <!-- Image Upload -->
                <div class="mb-4">
                  <label for="image" class="form-label fw-semibold">
                    <i class="bi bi-image me-1"></i>Featured Image
                  </label>
                  <div class="image-upload-area">
                    <!-- Image Preview -->
                    <div v-if="imagePreview" class="mb-3">
                      <div class="position-relative d-inline-block">
                        <img 
                          :src="imagePreview" 
                          alt="Preview" 
                          class="img-thumbnail"
                          style="max-height: 200px; max-width: 100%;"
                        >
                        <button
                          type="button"
                          class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                          @click="removeImage"
                        >
                          <i class="bi bi-x"></i>
                        </button>
                      </div>
                    </div>
                    
                    <!-- Upload Button -->
                    <div v-if="!imagePreview" class="upload-dropzone text-center p-4 border-2 border-dashed rounded">
                      <i class="bi bi-cloud-upload display-4 text-muted mb-2"></i>
                      <p class="mb-2">Drop an image here or click to browse</p>
                      <input
                        type="file"
                        id="image"
                        class="form-control d-none"
                        accept="image/*"
                        @change="handleImageUpload"
                        ref="imageInput"
                      >
                      <button
                        type="button"
                        class="btn btn-outline-primary"
                        @click="$refs.imageInput.click()"
                      >
                        <i class="bi bi-upload me-1"></i>Choose Image
                      </button>
                    </div>
                  </div>
                  <div class="form-text">
                    Supported formats: JPG, PNG, GIF (Max 2MB)
                  </div>
                  <div v-if="errors.image" class="text-danger mt-1">
                    {{ errors.image[0] }}
                  </div>
                </div>

                <!-- Content Editor -->
                <div class="mb-4">
                  <label for="content" class="form-label fw-semibold">
                    <i class="bi bi-text-paragraph me-1"></i>Content *
                  </label>
                  <div class="editor-toolbar mb-2">
                    <div class="btn-group btn-group-sm" role="group">
                      <button type="button" class="btn btn-outline-secondary" @click="formatText('bold')" title="Bold">
                        <i class="bi bi-type-bold"></i>
                      </button>
                      <button type="button" class="btn btn-outline-secondary" @click="formatText('italic')" title="Italic">
                        <i class="bi bi-type-italic"></i>
                      </button>
                      <button type="button" class="btn btn-outline-secondary" @click="formatText('underline')" title="Underline">
                        <i class="bi bi-type-underline"></i>
                      </button>
                    </div>
                    <div class="btn-group btn-group-sm ms-2" role="group">
                      <button type="button" class="btn btn-outline-secondary" @click="insertList('ul')" title="Bullet List">
                        <i class="bi bi-list-ul"></i>
                      </button>
                      <button type="button" class="btn btn-outline-secondary" @click="insertList('ol')" title="Numbered List">
                        <i class="bi bi-list-ol"></i>
                      </button>
                    </div>
                  </div>
                  <textarea
                    id="content"
                    class="form-control"
                    :class="{ 'is-invalid': errors.content }"
                    v-model="form.content"
                    placeholder="Write your post content here... You can use basic formatting."
                    rows="12"
                    required
                    ref="contentEditor"
                  ></textarea>
                  <div class="form-text">
                    {{ form.content.length }} characters | Use the toolbar above for basic formatting
                  </div>
                  <div v-if="errors.content" class="invalid-feedback">
                    {{ errors.content[0] }}
                  </div>
                </div>

                <!-- Post Settings -->
                <div class="row mb-4">
                  <div class="col-md-6">
                    <label for="status" class="form-label fw-semibold">
                      <i class="bi bi-eye me-1"></i>Post Status
                    </label>
                    <select
                      id="status"
                      class="form-select"
                      v-model="form.status"
                    >
                      <option value="published">
                        <i class="bi bi-globe"></i> Published (Public)
                      </option>
                      <option value="draft">
                        <i class="bi bi-file-earmark"></i> Draft (Private)
                      </option>
                    </select>
                    <div class="form-text">
                      Published posts are visible to everyone, drafts are private
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                  <div>
                    <button
                      type="button"
                      class="btn btn-outline-info me-2"
                      @click="showPreview = !showPreview"
                    >
                      <i class="bi bi-eye me-1"></i>
                      {{ showPreview ? 'Hide Preview' : 'Preview' }}
                    </button>
                  </div>
                  <div>
                    <button
                      type="button"
                      class="btn btn-outline-secondary me-2"
                      @click="saveDraft"
                      :disabled="loading || !form.title.trim()"
                    >
                      <i class="bi bi-save me-1"></i>Save Draft
                    </button>
                    <button
                      type="submit"
                      class="btn btn-primary"
                      :disabled="loading || !isFormValid"
                    >
                      <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                      <i v-else class="bi bi-send me-1"></i>
                      {{ form.status === 'published' ? 'Publish Post' : 'Save Draft' }}
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Preview Modal -->
      <div v-if="showPreview" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="bi bi-eye me-2"></i>Post Preview
              </h5>
              <button type="button" class="btn-close" @click="showPreview = false"></button>
            </div>
            <div class="modal-body">
              <article class="preview-content">
                <div v-if="imagePreview" class="mb-3">
                  <img :src="imagePreview" alt="Featured Image" class="img-fluid rounded">
                </div>
                <h1 class="h3 mb-3">{{ form.title || 'Untitled Post' }}</h1>
                <div class="post-meta mb-3 text-muted">
                  <small>
                    <i class="bi bi-person me-1"></i>{{ authStore.userName }}
                    <i class="bi bi-calendar ms-3 me-1"></i>{{ new Date().toLocaleDateString() }}
                    <span class="badge bg-secondary ms-2">{{ form.status }}</span>
                  </small>
                </div>
                <div class="post-content" v-html="formattedContent"></div>
              </article>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="showPreview = false">
                Close Preview
              </button>
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
import { computed, ref, reactive } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'CreatePostView',
  setup() {
    const authStore = useAuthStore()
    const postsStore = usePostsStore()
    const router = useRouter()
    
    const loading = ref(false)
    const showPreview = ref(false)
    const imagePreview = ref(null)
    const imageFile = ref(null)
    
    const form = reactive({
      title: '',
      content: '',
      status: 'published'
    })
    
    const errors = ref({})
    
    const isFormValid = computed(() => {
      return form.title.trim() && form.content.trim()
    })
    
    const formattedContent = computed(() => {
      return form.content.replace(/\n/g, '<br>')
    })
    
    const handleImageUpload = (event) => {
      const file = event.target.files[0]
      if (!file) return
      
      // Validate file size (2MB max)
      if (file.size > 2 * 1024 * 1024) {
        errors.value.image = ['Image size must be less than 2MB']
        return
      }
      
      // Validate file type
      if (!file.type.startsWith('image/')) {
        errors.value.image = ['Please select a valid image file']
        return
      }
      
      imageFile.value = file
      
      // Create preview
      const reader = new FileReader()
      reader.onload = (e) => {
        imagePreview.value = e.target.result
      }
      reader.readAsDataURL(file)
      
      // Clear any previous errors
      delete errors.value.image
    }
    
    const removeImage = () => {
      imagePreview.value = null
      imageFile.value = null
      if (this.$refs.imageInput) {
        this.$refs.imageInput.value = ''
      }
    }
    
    const formatText = (command) => {
      // Simple text formatting for textarea
      const textarea = this.$refs.contentEditor
      const start = textarea.selectionStart
      const end = textarea.selectionEnd
      const selectedText = form.content.substring(start, end)
      
      let formattedText = selectedText
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
      }
      
      form.content = form.content.substring(0, start) + formattedText + form.content.substring(end)
    }
    
    const insertList = (type) => {
      const textarea = this.$refs.contentEditor
      const start = textarea.selectionStart
      const listItem = type === 'ul' ? '- ' : '1. '
      const newText = '\n' + listItem
      
      form.content = form.content.substring(0, start) + newText + form.content.substring(start)
    }
    
    const saveDraft = async () => {
      form.status = 'draft'
      await handleSubmit()
    }
    
    const handleSubmit = async () => {
      if (!isFormValid.value) return
      
      loading.value = true
      errors.value = {}
      
      try {
        const postData = {
          title: form.title.trim(),
          content: form.content.trim(),
          status: form.status
        }
        
        // Handle image upload if present
        if (imageFile.value) {
          const formData = new FormData()
          formData.append('title', postData.title)
          formData.append('content', postData.content)
          formData.append('status', postData.status)
          formData.append('image', imageFile.value)
          
          await postsStore.createPost(formData, true) // true for FormData
        } else {
          await postsStore.createPost(postData)
        }
        
        // Success - redirect to home or post view
        router.push('/')
        
        // Show success message
        alert(`Post ${form.status === 'published' ? 'published' : 'saved as draft'} successfully!`)
        
      } catch (error) {
        console.error('Error creating post:', error)
        
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
        } else {
          alert('Error creating post. Please try again.')
        }
      } finally {
        loading.value = false
      }
    }
    
    return {
      authStore,
      postsStore,
      form,
      errors,
      loading,
      showPreview,
      imagePreview,
      isFormValid,
      formattedContent,
      handleImageUpload,
      removeImage,
      formatText,
      insertList,
      saveDraft,
      handleSubmit
    }
  }
}
</script>

<style scoped>
.create-post-page {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.upload-dropzone {
  border-color: #dee2e6;
  transition: all 0.3s ease;
  cursor: pointer;
}

.upload-dropzone:hover {
  border-color: #0d6efd;
  background-color: #f8f9ff;
}

.editor-toolbar {
  background-color: #f8f9fa;
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #dee2e6;
}

.preview-content {
  font-family: Georgia, serif;
  line-height: 1.6;
}

.preview-content h1 {
  color: #212529;
  margin-bottom: 1rem;
}

.preview-content .post-content {
  color: #495057;
  font-size: 1.1rem;
}

.card {
  border: none;
  border-radius: 12px;
}

.form-control:focus,
.form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn-primary {
  background: linear-gradient(45deg, #0d6efd, #0b5ed7);
  border: none;
}

.btn-primary:hover {
  background: linear-gradient(45deg, #0b5ed7, #0a58ca);
  transform: translateY(-1px);
}
</style>
