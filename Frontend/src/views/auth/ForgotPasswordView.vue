<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const email = ref('')
const isLoading = ref(false)
const isSent = ref(false)
const errorMessage = ref('')

const isFormValid = computed(() => email.value.trim() !== '')

const handleSubmit = async () => {
  if (!isFormValid.value) return

  isLoading.value = true
  errorMessage.value = ''

  try {
    // TODO: Wire up to your auth API
    // await authApi.forgotPassword({ email: email.value })
    await new Promise(resolve => setTimeout(resolve, 1500))
    isSent.value = true
  } catch (err) {
    errorMessage.value = err?.response?.data?.message || 'Something went wrong. Please try again.'
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

      <!-- Success state -->
      <template v-if="isSent">
        <div class="auth-heading">
          <h1 class="auth-heading__title">Check your email</h1>
          <p class="auth-heading__subtitle">
            We've sent a password reset link to <strong>{{ email }}</strong>. Check your inbox and follow the instructions.
          </p>
        </div>

        <button
          id="forgot-back"
          class="auth-submit"
          @click="router.push('/login')"
        >
          Back to Sign in
        </button>
      </template>

      <!-- Form state -->
      <template v-else>
        <div class="auth-heading">
          <h1 class="auth-heading__title">Forgot password?</h1>
          <p class="auth-heading__subtitle">
            No worries — enter your email and we'll send you a reset link.
          </p>
        </div>

        <!-- Error -->
        <div v-if="errorMessage" class="auth-error">
          <svg class="auth-error__icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
            <path d="M8 4.5v4M8 10.5v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          {{ errorMessage }}
        </div>

        <form @submit.prevent="handleSubmit" id="forgot-password-form">
          <div class="auth-field">
            <label class="auth-field__label" for="forgot-email">Email</label>
            <div class="auth-field__wrapper">
              <svg class="auth-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="3"/>
                <path d="M22 7l-8.97 5.7a1.94 1.94 0 01-2.06 0L2 7"/>
              </svg>
              <input
                id="forgot-email"
                v-model="email"
                type="email"
                class="auth-field__input"
                placeholder="you@example.com"
                autocomplete="email"
                required
              />
            </div>
          </div>

          <button
            id="forgot-submit"
            type="submit"
            class="auth-submit"
            :class="{ 'auth-submit--loading': isLoading }"
            :disabled="!isFormValid || isLoading"
          >
            <span v-if="isLoading" class="auth-submit__spinner"></span>
            {{ isLoading ? 'Sending...' : 'Send reset link' }}
          </button>
        </form>
      </template>

      <!-- Footer -->
      <div class="auth-footer">
        Remember your password? <router-link to="/login">Sign in</router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Page-specific overrides — layout handled by auth.css */
</style>
