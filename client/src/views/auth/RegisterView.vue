<template>
  <div class="register-page">
    <div class="container-fluid vh-100">
      <div class="row h-100">
        <!-- Left Side - Hero Image -->
        <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-primary">
          <div class="text-center text-white">
            <i class="bi bi-journal-plus display-1 mb-4"></i>
            <h2 class="mb-3">Join Our Community!</h2>
            <p class="lead">Start your blogging journey and share your stories with the world</p>
          </div>
        </div>
        
        <!-- Right Side - Register Form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center">
          <div class="register-form-container w-100" style="max-width: 400px;">
            <div class="text-center mb-4">
              <h1 class="h3 mb-3 fw-bold">Create Account</h1>
              <p class="text-muted">Join thousands of writers and readers</p>
            </div>

            <!-- Error Alert -->
            <div v-if="authStore.error" class="alert alert-danger" role="alert">
              <i class="bi bi-exclamation-triangle me-2"></i>
              {{ authStore.error }}
            </div>

            <!-- Register Form -->
            <form @submit.prevent="handleRegister" class="needs-validation" novalidate>
              <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="bi bi-person"></i>
                  </span>
                  <input
                    type="text"
                    class="form-control"
                    id="name"
                    v-model="form.name"
                    :class="{ 'is-invalid': errors.name }"
                    placeholder="Enter your full name"
                    required
                  >
                  <div v-if="errors.name" class="invalid-feedback">
                    {{ errors.name }}
                  </div>
                </div>
              </div>

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
                    placeholder="Create a password"
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
                <div class="form-text">
                  Password must be at least 8 characters long
                </div>
              </div>

              <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="bi bi-lock-fill"></i>
                  </span>
                  <input
                    :type="showConfirmPassword ? 'text' : 'password'"
                    class="form-control"
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :class="{ 'is-invalid': errors.password_confirmation }"
                    placeholder="Confirm your password"
                    required
                  >
                  <button
                    type="button"
                    class="btn btn-outline-secondary"
                    @click="showConfirmPassword = !showConfirmPassword"
                  >
                    <i :class="showConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                  </button>
                  <div v-if="errors.password_confirmation" class="invalid-feedback">
                    {{ errors.password_confirmation }}
                  </div>
                </div>
              </div>

              <div class="mb-3 form-check">
                <input
                  type="checkbox"
                  class="form-check-input"
                  id="terms"
                  v-model="form.acceptTerms"
                  :class="{ 'is-invalid': errors.acceptTerms }"
                  required
                >
                <label class="form-check-label" for="terms">
                  I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and 
                  <a href="#" class="text-decoration-none">Privacy Policy</a>
                </label>
                <div v-if="errors.acceptTerms" class="invalid-feedback">
                  {{ errors.acceptTerms }}
                </div>
              </div>

              <button
                type="submit"
                class="btn btn-primary w-100 mb-3"
                :disabled="authStore.loading"
              >
                <span v-if="authStore.loading" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-person-plus me-2"></i>
                {{ authStore.loading ? 'Creating Account...' : 'Create Account' }}
              </button>
            </form>

            <!-- Divider -->
            <div class="text-center mb-3">
              <span class="text-muted">Already have an account?</span>
            </div>

            <!-- Login Link -->
            <router-link to="/login" class="btn btn-outline-primary w-100">
              <i class="bi bi-box-arrow-in-right me-2"></i>
              Sign In
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
  name: 'RegisterView',
  setup() {
    const authStore = useAuthStore()
    const router = useRouter()
    
    const showPassword = ref(false)
    const showConfirmPassword = ref(false)
    
    const form = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      acceptTerms: false
    })
    
    const errors = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      acceptTerms: ''
    })
    
    const validateForm = () => {
      // Reset errors
      Object.keys(errors).forEach(key => {
        errors[key] = ''
      })
      
      let isValid = true
      
      // Name validation
      if (!form.name.trim()) {
        errors.name = 'Full name is required'
        isValid = false
      } else if (form.name.trim().length < 2) {
        errors.name = 'Name must be at least 2 characters'
        isValid = false
      }
      
      // Email validation
      if (!form.email) {
        errors.email = 'Email is required'
        isValid = false
      } else if (!isValidEmail(form.email)) {
        errors.email = 'Please enter a valid email address'
        isValid = false
      }
      
      // Password validation
      if (!form.password) {
        errors.password = 'Password is required'
        isValid = false
      } else if (form.password.length < 8) {
        errors.password = 'Password must be at least 8 characters'
        isValid = false
      }
      
      // Password confirmation validation
      if (!form.password_confirmation) {
        errors.password_confirmation = 'Please confirm your password'
        isValid = false
      } else if (form.password !== form.password_confirmation) {
        errors.password_confirmation = 'Passwords do not match'
        isValid = false
      }
      
      // Terms validation
      if (!form.acceptTerms) {
        errors.acceptTerms = 'You must accept the terms and conditions'
        isValid = false
      }
      
      return isValid
    }
    
    const isValidEmail = (email) => {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return emailRegex.test(email)
    }
    
    const handleRegister = async () => {
      if (!validateForm()) return
      
      try {
        await authStore.register({
          name: form.name.trim(),
          email: form.email,
          password: form.password,
          password_confirmation: form.password_confirmation
        })
        
        // Redirect to home after successful registration
        router.push('/')
      } catch (error) {
        console.error('Registration error:', error)
      }
    }
    
    onMounted(() => {
      // Clear any previous errors
      authStore.clearError()
    })
    
    return {
      authStore,
      showPassword,
      showConfirmPassword,
      form,
      errors,
      handleRegister
    }
  }
}
</script>

<style scoped>
.register-page {
  background-color: #f8f9fa;
}

.bg-primary {
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
}

.register-form-container {
  padding: 2rem;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  margin: 1rem;
  max-height: 90vh;
  overflow-y: auto;
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

.form-check-input.is-invalid {
  border-color: #dc3545;
}

@media (max-width: 991.98px) {
  .register-form-container {
    margin: 1rem;
    padding: 1.5rem;
  }
  
  .container-fluid {
    padding: 0;
  }
}
</style>
