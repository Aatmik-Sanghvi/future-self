<script setup>
import { ref, computed, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

// State
const currentStep = ref('email') // 'email' | 'otp' | 'reset'
const errorMessage = ref('')
const isLoading = ref(false)

// Step 1: Email
const email = ref('')
const isEmailValid = computed(() => email.value.trim() !== '')

const handleSendEmail = async () => {
  if (!isEmailValid.value) return
  isLoading.value = true
  errorMessage.value = ''

  try {
    await auth.forgotPassword({ email: email.value })
    auth.toastMessage('Password reset OTP sent to your email.', { type: 'success' })
    startCountdown()
    currentStep.value = 'otp'
    await nextTick()
    focusOtpInput(0)
  } catch (err) {
    errorMessage.value = err?.response?.data?.message || 'Something went wrong. Please try again.'
  } finally {
    isLoading.value = false
  }
}

// Step 2: OTP
const otp = ref(['', '', '', ''])
const otpInputs = ref([])
const resendCooldown = ref(60)
let timerInterval = null
const resetToken = ref('')

const maskedEmail = computed(() => {
  if (!email.value) return ''
  const [name, domain] = email.value.split('@')
  if (name.length <= 2) return email.value
  return `${name[0]}***${name[name.length - 1]}@${domain}`
})

const isOtpValid = computed(() => otp.value.every(digit => digit !== ''))

const handleOtpInput = (index, event) => {
  const value = event.target.value
  // Allow only numbers
  if (!/^\d*$/.test(value)) {
    otp.value[index] = ''
    return
  }
  
  // Update value (handle if multiple characters pasted in single field fallback)
  if (value.length > 1) {
    otp.value[index] = value[0]
  }

  // Move to next input if not empty and not the last box
  if (otp.value[index] !== '' && index < 3) {
    focusOtpInput(index + 1)
  }

  // Auto submit when all filled
  if (isOtpValid.value) {
    handleVerifyOtp()
  }
}

const handleOtpKeydown = (index, event) => {
  // Move to previous on Backspace if current is empty
  if (event.key === 'Backspace' && otp.value[index] === '' && index > 0) {
    focusOtpInput(index - 1)
  }
}

const handleOtpPaste = (event) => {
  event.preventDefault()
  const pastedData = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 4)
  if (pastedData) {
    const chars = pastedData.split('')
    for (let i = 0; i < chars.length; i++) {
      otp.value[i] = chars[i]
    }
    // Focus next empty or last
    const nextEmpty = otp.value.findIndex(val => val === '')
    if (nextEmpty !== -1) {
      focusOtpInput(nextEmpty)
    } else {
      focusOtpInput(3)
      handleVerifyOtp()
    }
  }
}

const focusOtpInput = (index) => {
  if (otpInputs.value[index]) {
    otpInputs.value[index].focus()
  }
}

const startCountdown = () => {
  resendCooldown.value = 60
  clearInterval(timerInterval)
  timerInterval = setInterval(() => {
    if (resendCooldown.value > 0) {
      resendCooldown.value--
    } else {
      clearInterval(timerInterval)
    }
  }, 1000)
}

const handleResendOtp = async () => {
  if (resendCooldown.value > 0) return
  isLoading.value = true
  errorMessage.value = ''
  otp.value = ['', '', '', ''] // Clear OTP

  try {
    await auth.forgotPassword({ email: email.value })
    auth.toastMessage('A new OTP has been sent to your email.', { type: 'success' })
    startCountdown()
    focusOtpInput(0)
  } catch (err) {
    errorMessage.value = err?.response?.data?.message || 'Failed to resend OTP.'
  } finally {
    isLoading.value = false
  }
}

const handleVerifyOtp = async () => {
  if (!isOtpValid.value) return
  isLoading.value = true
  errorMessage.value = ''

  try {
    const otpString = otp.value.join('')
    const response = await auth.verifyOtp({ 
      email: email.value, 
      otp: otpString 
    })
    
    resetToken.value = response.data.reset_token
    auth.toastMessage('OTP verified successfully.', { type: 'success' })
    currentStep.value = 'reset'
  } catch (err) {
    errorMessage.value = err?.response?.data?.message || 'Invalid OTP. Please try again.'
    // Shake animation could be triggered here by adding a class to otp-group
    otp.value = ['', '', '', ''] // clear on failure
    focusOtpInput(0)
  } finally {
    isLoading.value = false
  }
}

// Step 3: Reset Password
const password = ref('')
const passwordConfirm = ref('')
const showPassword = ref(false)

const isResetValid = computed(() => {
  return password.value.length >= 8 && password.value === passwordConfirm.value
})

const passwordStrength = computed(() => {
  let score = 0;
  if (!password.value) return 0;
  if (password.value.length >= 8) score++;
  if (/[A-Z]/.test(password.value)) score++;
  if (/[0-9]/.test(password.value)) score++;
  if (/[^A-Za-z0-9]/.test(password.value)) score++;
  return score; // 0 to 4
})

const togglePassword = () => {
  showPassword.value = !showPassword.value
}

const handleResetPassword = async () => {
  if (!isResetValid.value) return
  isLoading.value = true
  errorMessage.value = ''

  try {
    await auth.resetPassword({
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirm.value,
      reset_token: resetToken.value
    })

    auth.toastMessage('Password reset successfully! Please sign in with your new password.', { type: 'success' })
    router.push({ name: 'login' })
  } catch (err) {
    errorMessage.value = err?.response?.data?.message || 'Failed to reset password.'
  } finally {
    isLoading.value = false
  }
}

// Cleanup
onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})
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

      <!-- Error -->
      <div v-if="errorMessage" class="auth-error">
        <svg class="auth-error__icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
          <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
          <path d="M8 4.5v4M8 10.5v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        {{ errorMessage }}
      </div>

      <div class="step-wrapper">
        <Transition name="step" mode="out-in">
          
          <!-- Step 1: Email -->
          <div v-if="currentStep === 'email'" key="step-email">
            <div class="auth-heading">
              <h1 class="auth-heading__title">Forgot password?</h1>
              <p class="auth-heading__subtitle">
                No worries — enter your email and we'll send you an OTP to reset it.
              </p>
            </div>

            <form @submit.prevent="handleSendEmail" id="forgot-password-form">
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
                :disabled="!isEmailValid || isLoading"
              >
                <span v-if="isLoading" class="auth-submit__spinner"></span>
                {{ isLoading ? 'Sending OTP...' : 'Send OTP' }}
              </button>
            </form>

            <div class="auth-footer">
              Remember your password? <router-link to="/login">Sign in</router-link>
            </div>
          </div>

          <!-- Step 2: OTP -->
          <div v-else-if="currentStep === 'otp'" key="step-otp">
            <div class="auth-heading">
              <h1 class="auth-heading__title">Verify it's you</h1>
              <p class="auth-heading__subtitle">
                We've sent a 4-digit code to
                <br>
                <span class="otp-email-badge">{{ maskedEmail }}</span>
              </p>
            </div>

            <form @submit.prevent="handleVerifyOtp" id="otp-form">
              <div class="otp-container">
                <div class="otp-group">
                  <input
                    v-for="(_, index) in 4"
                    :key="index"
                    ref="otpInputs"
                    v-model="otp[index]"
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    class="otp-input"
                    :disabled="isLoading"
                    @input="handleOtpInput(index, $event)"
                    @keydown="handleOtpKeydown(index, $event)"
                    @paste="handleOtpPaste"
                  />
                </div>
                
                <div class="otp-meta">
                  <span class="otp-timer" :class="{'otp-timer--warning': resendCooldown <= 10}">
                    {{ resendCooldown > 0 ? `00:${resendCooldown.toString().padStart(2, '0')}` : 'Code expired' }}
                  </span>
                  <button 
                    type="button" 
                    class="otp-resend" 
                    :disabled="resendCooldown > 0 || isLoading"
                    @click="handleResendOtp"
                  >
                    Resend Code
                  </button>
                </div>
              </div>

              <button
                id="verify-submit"
                type="submit"
                class="auth-submit"
                :class="{ 'auth-submit--loading': isLoading }"
                :disabled="!isOtpValid || isLoading"
              >
                <span v-if="isLoading" class="auth-submit__spinner"></span>
                {{ isLoading ? 'Verifying...' : 'Verify Code' }}
              </button>
            </form>
            
            <div class="auth-footer">
              <a href="#" @click.prevent="currentStep = 'email'">Change email address</a>
            </div>
          </div>

          <!-- Step 3: Reset Password -->
          <div v-else-if="currentStep === 'reset'" key="step-reset">
            <div class="auth-heading">
              <h1 class="auth-heading__title">Create new password</h1>
              <p class="auth-heading__subtitle">
                Your new password must be different from previously used passwords.
              </p>
            </div>

            <form @submit.prevent="handleResetPassword" id="reset-password-form">
              <div class="auth-field">
                <label class="auth-field__label" for="reset-password">New Password</label>
                <div class="auth-field__wrapper">
                  <svg class="auth-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="3"/>
                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                  </svg>
                  <input
                    id="reset-password"
                    v-model="password"
                    :type="showPassword ? 'text' : 'password'"
                    class="auth-field__input"
                    placeholder="Enter new password"
                    required
                  />
                  <button type="button" class="auth-field__toggle" @click="togglePassword" aria-label="Toggle password visibility">
                    <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                      <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                  </button>
                </div>
                
                <div class="password-strength" v-if="password">
                  <div class="password-strength__segment" :class="{ 'password-strength__segment--active strength-weak': passwordStrength >= 1 }"></div>
                  <div class="password-strength__segment" :class="{ 'password-strength__segment--active strength-fair': passwordStrength >= 2 }"></div>
                  <div class="password-strength__segment" :class="{ 'password-strength__segment--active strength-good': passwordStrength >= 3 }"></div>
                  <div class="password-strength__segment" :class="{ 'password-strength__segment--active strength-strong': passwordStrength >= 4 }"></div>
                </div>
              </div>

              <div class="auth-field">
                <label class="auth-field__label" for="reset-password-confirm">Confirm Password</label>
                <div class="auth-field__wrapper">
                  <svg class="auth-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="3"/>
                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                  </svg>
                  <input
                    id="reset-password-confirm"
                    v-model="passwordConfirm"
                    :type="showPassword ? 'text' : 'password'"
                    class="auth-field__input"
                    placeholder="Confirm new password"
                    required
                  />
                </div>
              </div>

              <button
                id="reset-submit"
                type="submit"
                class="auth-submit"
                :class="{ 'auth-submit--loading': isLoading }"
                :disabled="!isResetValid || isLoading"
              >
                <span v-if="isLoading" class="auth-submit__spinner"></span>
                {{ isLoading ? 'Resetting...' : 'Reset Password' }}
              </button>
            </form>
          </div>

        </Transition>
      </div>

    </div>
  </div>
</template>

<style scoped>
/* Page-specific overrides — layout handled by auth.css */
</style>
