import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('auth_token') || null,
    loading: false,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
    userName: (state) => state.user?.name || '',
    userEmail: (state) => state.user?.email || ''
  },

  actions: {
    async register(userData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.post('/register', userData)
        
        this.user = response.data.user
        this.token = response.data.token
        
        localStorage.setItem('user', JSON.stringify(response.data.user))
        localStorage.setItem('auth_token', response.data.token)
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed'
        throw error
      } finally {
        this.loading = false
      }
    },

    async login(credentials) {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.post('/login', credentials)
        
        this.user = response.data.user
        this.token = response.data.token
        
        localStorage.setItem('user', JSON.stringify(response.data.user))
        localStorage.setItem('auth_token', response.data.token)
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed'
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        if (this.token) {
          await axios.post('/logout')
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.token = null
        this.error = null
        
        localStorage.removeItem('user')
        localStorage.removeItem('auth_token')
      }
    },

    async fetchUser() {
      if (!this.token) return
      
      try {
        const response = await axios.get('/user')
        this.user = response.data.user
        localStorage.setItem('user', JSON.stringify(response.data.user))
      } catch (error) {
        console.error('Fetch user error:', error)
        this.logout()
      }
    },

    clearError() {
      this.error = null
    }
  }
})
