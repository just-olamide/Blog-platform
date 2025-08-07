import { defineStore } from 'pinia'
import axios from 'axios'

export const usePostsStore = defineStore('posts', {
  state: () => ({
    posts: [],
    currentPost: null,
    myPosts: [],
    savedPosts: [],
    likedPosts: [],
    loading: false,
    error: null,
    pagination: {
      current_page: 1,
      last_page: 1,
      total: 0
    }
  }),

  actions: {
    async fetchPosts(page = 1, search = '') {
      this.loading = true
      this.error = null
      
      try {
        const params = { page }
        if (search) params.search = search
        
        const response = await axios.get('/posts', { params })
        
        this.posts = response.data.data
        this.pagination = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          total: response.data.total
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch posts'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchPost(id) {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.get(`/posts/${id}`)
        this.currentPost = response.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch post'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createPost(postData) {
      this.loading = true
      this.error = null
      
      try {
        const formData = new FormData()
        Object.keys(postData).forEach(key => {
          if (postData[key] !== null && postData[key] !== undefined) {
            formData.append(key, postData[key])
          }
        })
        
        const response = await axios.post('/posts', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        // Add to posts array if it's published
        if (response.data.post.status === 'published') {
          this.posts.unshift(response.data.post)
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create post'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updatePost(id, postData) {
      this.loading = true
      this.error = null
      
      try {
        const formData = new FormData()
        Object.keys(postData).forEach(key => {
          if (postData[key] !== null && postData[key] !== undefined) {
            formData.append(key, postData[key])
          }
        })
        
        const response = await axios.post(`/posts/${id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-HTTP-Method-Override': 'PUT'
          }
        })
        
        // Update in posts array
        const index = this.posts.findIndex(post => post.id === id)
        if (index !== -1) {
          this.posts[index] = response.data.post
        }
        
        // Update current post if it's the same
        if (this.currentPost?.id === id) {
          this.currentPost = response.data.post
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update post'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deletePost(id) {
      this.loading = true
      this.error = null
      
      try {
        await axios.delete(`/posts/${id}`)
        
        // Remove from posts array
        this.posts = this.posts.filter(post => post.id !== id)
        
        // Clear current post if it's the same
        if (this.currentPost?.id === id) {
          this.currentPost = null
        }
        
        return true
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete post'
        throw error
      } finally {
        this.loading = false
      }
    },

    async toggleLike(postId) {
      try {
        const response = await axios.post(`/posts/${postId}/like`)
        
        // Update both like status and count in posts arrays
        this.updatePostInteraction(postId, 'likes_count', response.data.likes_count)
        this.updatePostInteraction(postId, 'is_liked', response.data.liked)
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to toggle like'
        throw error
      }
    },

    async toggleSave(postId) {
      try {
        const response = await axios.post(`/posts/${postId}/save`)
        
        // Update post in arrays
        this.updatePostInteraction(postId, 'saves_count', response.data.saves_count)
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to toggle save'
        throw error
      }
    },

    async toggleRepost(postId) {
      try {
        const response = await axios.post(`/posts/${postId}/repost`)
        
        // Update both repost status and count in posts arrays
        this.updatePostInteraction(postId, 'reposts_count', response.data.reposts_count)
        this.updatePostInteraction(postId, 'is_reposted', response.data.reposted)
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to toggle repost'
        throw error
      }
    },

    async addComment(postId, content) {
      try {
        const response = await axios.post(`/posts/${postId}/comments`, { content })
        
        // Update comments count
        this.updatePostInteraction(postId, 'comments_count', 
          (this.getPostById(postId)?.comments_count || 0) + 1)
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to add comment'
        throw error
      }
    },

    async fetchMyPosts() {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.get('/my-posts')
        this.myPosts = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch posts'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchSavedPosts() {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.get('/saved-posts')
        this.savedPosts = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch saved posts'
        throw error
      } finally {
        this.loading = false
      }
    },

    async getPost(id) {
      try {
        const response = await axios.get(`/posts/${id}`)
        this.currentPost = response.data.post
        return response.data.post
      } catch (error) {
        this.error = error.response?.data?.message || 'Post not found'
        throw error
      }
    },

    async getPostComments(postId) {
      try {
        const response = await axios.get(`/posts/${postId}/comments`)
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch comments'
        throw error
      }
    },

    async toggleLike(postId) {
      try {
        const response = await axios.post(`/posts/${postId}/like`)
        
        // Update both like status and count in posts arrays
        this.updatePostInteraction(postId, 'likes_count', response.data.likes_count)
        this.updatePostInteraction(postId, 'is_liked', response.data.liked)
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to toggle like'
        throw error
      }
    },

    // Helper methods
    updatePostInteraction(postId, field, value) {
      // Update in posts array
      const postIndex = this.posts.findIndex(post => post.id === postId)
      if (postIndex !== -1) {
        this.posts[postIndex][field] = value
      }
      
      // Update current post
      if (this.currentPost?.id === postId) {
        this.currentPost[field] = value
      }
    },

    getPostById(id) {
      return this.posts.find(post => post.id === id) || this.currentPost
    },

    clearError() {
      this.error = null
    }
  }
})
