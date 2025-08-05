<template>
  <div class="login-page">
    <div class="container-fluid vh-100">
      <div class="row h-100">
        <!-- Left Side - Hero Image -->
        <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-primary">
          <div class="text-center text-white">
            <i class="bi bi-journal-bookmark display-1 mb-4"></i>
            <h2 class="mb-3">Welcome Back!</h2>
            <p class="lead">Continue your blogging journey with us</p>
          </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center">
          <div class="login-form-container w-100" style="max-width: 400px;">
            <div class="text-center mb-4">
              <h1 class="h3 mb-3 fw-bold">Sign In</h1>
              <p class="text-muted">Enter your credentials to access your account</p>
            </div>

            <!-- Error Alert -->
            <div v-if="authStore.error" class="alert alert-danger" role="alert">
              <i class="bi bi-exclamation-triangle me-2"></i>
              {{ authStore.error }}
            </div>

            <!-- Login Form -->
            <form @submit.prevent="handleLogin" class="needs-validation" novalidate>
              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="bi bi-envelope"></i>
                  </span>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    v-model="form.email"
                    :class="{ 'is-invalid': errors.email }"
                    placeholder="Enter your email"
                    required
                  >
                  <div v-if="errors.email" class="invalid-feedback">
                    {{ errors.email }}
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="bi bi-lock"></i>
                  </span>
                  <input
                    :type="showPassword ? 'text' : 'password'"
                    class="form-control"
                    id="password"
                    v-model="form.password"
                    :class="{ 'is-invalid': errors.password }"
                    placeholder="Enter your password"
                    required
                  >
                  <button
                    type="button"
                    class="btn btn-outline-secondary"
                    @click="showPassword = !showPassword"
                  >
                    <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                  </button>
                  <div v-if="errors.password" class="invalid-feedback">
                    {{ errors.password }}
                  </div>
                </div>
              </div>

              <div class="mb-3 form-check">
                <input
                  type="checkbox"
                  class="form-check-input"
                  id="remember"
                  v-model="form.remember"
                >
                <label class="form-check-label" for="remember">
                  Remember me
                </label>
              </div>

              <button
                type="submit"
                class="btn btn-primary w-100 mb-3"
                :disabled="authStore.loading"
              >
                <span v-if="authStore.loading" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-box-arrow-in-right me-2"></i>
                {{ authStore.loading ? 'Signing In...' : 'Sign In' }}
              </button>
            </form>

            <!-- Divider -->
            <div class="text-center mb-3">
              <span class="text-muted">Don't have an account?</span>
            </div>

            <!-- Register Link -->
            <router-link to="/register" class="btn btn-outline-primary w-100">
              <i class="bi bi-person-plus me-2"></i>
              Create Account
            </router-link>

            <!-- Back to Home -->
            <div class="text-center mt-4">
              <router-link to="/" class="text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Home
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth'
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'LoginView',
  setup() {
    const authStore = useAuthStore()
    const router = useRouter()
    
    const showPassword = ref(false)
    const form = reactive({
      email: '',
      password: '',
      remember: false
    })
    
    const errors = reactive({
      email: '',
      password: ''
    })
    
    const validateForm = () => {
      errors.email = ''
      errors.password = ''
      
      if (!form.email) {
        errors.email = 'Email is required'
        return false
      }
      
      if (!form.email.includes('@')) {
        errors.email = 'Please enter a valid email address'
        return false
      }
      
      if (!form.password) {
        errors.password = 'Password is required'
        return false
      }
      
      if (form.password.length < 6) {
        errors.password = 'Password must be at least 6 characters'
        return false
      }
      
      return true
    }
    
    const handleLogin = async () => {
      if (!validateForm()) return
      
      try {
        await authStore.login({
          email: form.email,
          password: form.password
        })
        
        // Redirect to home or intended page
        router.push('/')
      } catch (error) {
        console.error('Login error:', error)
      }
    }
    
    onMounted(() => {
      // Clear any previous errors
      authStore.clearError()
    })
    
    return {
      authStore,
      showPassword,
      form,
      errors,
      handleLogin
    }
  }
}
</script>

<style scoped>
.login-page {
  background-color: #f8f9fa;
}

.bg-primary {
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
}

.login-form-container {
  padding: 2rem;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  margin: 1rem;
}

.input-group-text {
  background-color: #f8f9fa;
  border-right: none;
}

.form-control {
  border-left: none;
}

.form-control:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-primary {
  background: linear-gradient(45deg, #007bff, #0056b3);
  border: none;
  padding: 0.75rem;
  font-weight: 500;
}

.btn-primary:hover {
  background: linear-gradient(45deg, #0056b3, #004085);
  transform: translateY(-1px);
}

.btn-outline-primary {
  border-color: #007bff;
  color: #007bff;
}

.btn-outline-primary:hover {
  background-color: #007bff;
  border-color: #007bff;
  transform: translateY(-1px);
}

@media (max-width: 991.98px) {
  .login-form-container {
    margin: 1rem;
    padding: 1.5rem;
  }
  
  .container-fluid {
    padding: 0;
  }
}
</style>
