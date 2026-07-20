<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const statusMessage = ref('Authenticating with Google...')
const isError = ref(false)

onMounted(async () => {
  const token = route.query.token
  const errorParam = route.query.error

  if (errorParam) {
    isError.value = true
    statusMessage.value = errorParam || 'Social login failed.'
    auth.toastMessage(statusMessage.value, {type:'error'})
    setTimeout(() => {
      router.push({ name: 'Login' })
    }, 2000)
    return
  }

  if (token) {
    try {
      auth.token = token
      localStorage.setItem('token', token)
      const user = await auth.fetchUser()
      
      auth.toastMessage('Signed in with Google successfully!', {type:'success'})

      const isOnboarded = user?.is_onboarded || route.query.is_onboarded === '1'
      router.push({ name: isOnboarded ? 'Dashboard' : 'Onboarding' })
    } catch (err) {
      console.error('Callback auth error:', err)
      isError.value = true
      statusMessage.value = 'Failed to load user profile. Please try logging in again.'
      auth.toastMessage(statusMessage.value, {type:'error'})
      setTimeout(() => {
        router.push({ name: 'Login' })
      }, 2000)
    }
  } else {
    isError.value = true
    statusMessage.value = 'Invalid callback parameters.'
    auth.toastMessage(statusMessage.value, {type:'error'})
    setTimeout(() => {
      router.push({ name: 'Login' })
    }, 2000)
  }
})
</script>

<template>
  <div class="auth-page">
    <div class="auth-card text-center flex-col items-center justify-center py-12">
      <div class="auth-brand mb-6">
        <div class="auth-brand__icon">✦</div>
        <div class="auth-brand__text">
          <div class="auth-brand__name">Future Self</div>
          <div class="auth-brand__tagline">Connecting your account...</div>
        </div>
      </div>

      <div class="spinner-container my-6">
        <div v-if="!isError" class="auth-spinner"></div>
        <div v-else class="text-red-400 text-3xl">⚠️</div>
      </div>

      <p class="text-lg text-slate-300 mt-4">{{ statusMessage }}</p>
    </div>
  </div>
</template>

<style scoped>
.auth-spinner {
  width: 44px;
  height: 44px;
  border: 4px solid rgba(255, 255, 255, 0.1);
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 0.9s linear infinite;
  margin: 0 auto;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
