<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { login } from '@/api/auth'

const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

const isFormValid = computed(() => {
  return email.value.trim() !== '' && password.value.length >= 1
})

const togglePassword = () => {
  showPassword.value = !showPassword.value
}

const handleLogin = async () => {
  if (!isFormValid.value) return

  isLoading.value = true
  validationErrors.value = {}
  errorMessage.value = ''

  try {
    // TODO: Wire up to your auth API
    const response = await login({ 
      email: email.value, 
      password: password.value 
    })

    // console.log(response.data)
    // await new Promise(resolve => setTimeout(resolve, 1500))
    router.push({ path: '/dashboard', query: { source: 'login' } })
  } catch (err) {
    if (err.response?.status === 422) {
        validationErrors.value = err.response.data.errors
    } else {
        errorMessage.value =
            err.response?.data?.message ||
            'Something went wrong.'
    }

  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">
      <!-- Brand -->
      <div class="auth-brand">
        <div class="auth-brand__icon">✦</div>
        <div class="auth-brand__text">
          <div class="auth-brand__name">FutureYou</div>
          <div class="auth-brand__tagline">Your future self is waiting</div>
        </div>
      </div>

      <!-- Heading -->
      <div class="auth-heading">
        <h1 class="auth-heading__title">Welcome back</h1>
        <p class="auth-heading__subtitle">Continue your journey forward</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" id="login-form">
        <!-- Email -->
        <div class="auth-field">
          <label class="auth-field__label" for="login-email">Email</label>
          <div class="auth-field__wrapper">
            <svg class="auth-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="4" width="20" height="16" rx="3"/>
              <path d="M22 7l-8.97 5.7a1.94 1.94 0 01-2.06 0L2 7"/>
            </svg>
            <input
              id="login-email"
              v-model="email"
              type="email"
              class="auth-field__input"
              placeholder="you@example.com"
              autocomplete="email"
            />
          </div>
          <p
              v-if="validationErrors.email"
              class="text-red-500 text-sm"
          >
              {{ validationErrors.email[0] }}
          </p>
        </div>

        <!-- Password -->
        <div class="auth-field">
          <label class="auth-field__label" for="login-password">Password</label>
          <div class="auth-field__wrapper">
            <svg class="auth-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="3"/>
              <path d="M7 11V7a5 5 0 0110 0v4"/>
            </svg>
            <input
              id="login-password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              class="auth-field__input"
              placeholder="Enter your password"
              autocomplete="current-password"
              required
            />
            <button type="button" class="auth-field__toggle" @click="togglePassword" aria-label="Toggle password visibility">
              <!-- Eye open -->
              <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <!-- Eye closed -->
              <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
          <p
                v-if="validationErrors.password"
                class="text-red-500 text-sm"
            >
                {{ validationErrors.password[0] }}
            </p>
          <div class="auth-field__action">
            <router-link to="/forgot-password">Forgot password?</router-link>
          </div>
        </div>

        <!-- Submit -->
        <button
          id="login-submit"
          type="submit"
          class="auth-submit"
          :class="{ 'auth-submit--loading': isLoading }"
          :disabled="!isFormValid || isLoading"
        >
          <span v-if="isLoading" class="auth-submit__spinner"></span>
          {{ isLoading ? 'Signing in...' : 'Sign in' }}
        </button>
      </form>

      <!-- Divider -->
      <div class="auth-divider">
        <div class="auth-divider__line"></div>
        <span class="auth-divider__text">or continue with</span>
        <div class="auth-divider__line"></div>
      </div>

      <!-- Social logins -->
      <div class="auth-socials">
        <button id="login-google" class="auth-social-btn" type="button">
          <svg class="auth-social-btn__icon" viewBox="0 0 24 24">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
          </svg>
          Google
        </button>
        <button id="login-apple" class="auth-social-btn" type="button">
          <svg class="auth-social-btn__icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.05 20.28c-.98.95-2.05.88-3.08.4-1.09-.5-2.08-.51-3.23 0-1.44.62-2.2.44-3.06-.4C3.79 16.17 4.36 9.02 8.7 8.76c1.26.06 2.14.72 2.88.76.99-.2 1.94-.78 3-.84 1.28-.08 2.25.47 2.88 1.21-2.65 1.56-2.02 5.01.36 5.97-.47 1.24-.73 1.81-1.36 2.89-.53.91-1.28 1.82-2.2 2.79l-.21-.26zM12.05 8.68c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
          </svg>
          Apple
        </button>
      </div>

      <!-- Footer -->
      <div class="auth-footer">
        New here? <router-link to="/register">Create your Future Self</router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Page-specific overrides — layout handled by auth.css */
</style>
