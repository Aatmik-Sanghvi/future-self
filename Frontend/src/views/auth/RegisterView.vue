<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import CountryFlag from 'vue-country-flag-next'
import countryTelephoneData from 'country-telephone-data'
import { useAuthStore } from '@/stores/auth'
import AuthService from '@/services/authService'
import LegalModal from '@/components/LegalModal.vue'

const router = useRouter()
const auth = useAuthStore()

// Step management
const currentStep = ref('form') // 'form' | 'otp'

const isLegalModalOpen = ref(false)
const legalModalTab = ref('terms')

const openLegalModal = (tab = 'terms') => {
  legalModalTab.value = tab
  isLegalModalOpen.value = true
}

const handleGoogleLogin = async () => {
  try {
    await AuthService.redirectToGoogle()
  } catch (err) {
    console.error(err)
    const message = err?.response?.data?.message || err?.message || 'Failed to initiate Google sign in.'
    auth.toastMessage(message, { type: 'error' })
  }
}

const name = ref('')
const email = ref('')
const mobile = ref('')
const selectedCountryIso2 = ref('in')
const countryMenuOpen = ref(false)
const countryMenuRef = ref(null)
const password = ref('')
const confirmPassword = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const agreedToTerms = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

const handleOutsideClick = (event) => {
  if (countryMenuOpen.value && countryMenuRef.value && !countryMenuRef.value.contains(event.target)) {
    countryMenuOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleOutsideClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleOutsideClick)
  if (timerInterval) clearInterval(timerInterval)
})

const toggleCountryMenu = () => {
  countryMenuOpen.value = !countryMenuOpen.value
}

const selectCountry = (iso2) => {
  selectedCountryIso2.value = iso2
  countryMenuOpen.value = false
}

const countryOptions = (() => {
  const entries = new Map()
  ;(countryTelephoneData.allCountries || [])
    .filter((country) => country.dialCode)
    .forEach((country) => {
      const dialCode = `+${country.dialCode}`
      if (!entries.has(dialCode)) {
        entries.set(dialCode, {
          iso2: country.iso2,
          dialCode,
          label: dialCode,
        })
      }
    })
  return Array.from(entries.values()).sort((a, b) => {
    const aCode = parseInt(a.dialCode.replace('+', ''), 10)
    const bCode = parseInt(b.dialCode.replace('+', ''), 10)
    return aCode - bCode
  })
})()

const selectedCountry = computed(() =>
  countryOptions.find((option) => option.iso2 === selectedCountryIso2.value) || countryOptions[0]
)

const countryCode = computed(() => selectedCountry.value?.dialCode || '+1')
const mobileNumber = computed(() => mobile.value.trim())
const fullMobile = computed(() => `${countryCode.value}${mobileNumber.value.replace(/^\+/, '')}`)

const passwordStrength = computed(() => {
  const val = password.value
  if (!val) return { level: 0, label: '', class: '' }
  let score = 0
  if (val.length >= 8) score++
  if (/[A-Z]/.test(val)) score++
  if (/[0-9]/.test(val)) score++
  if (/[^A-Za-z0-9]/.test(val)) score++

  const levels = [
    { level: 0, label: '', class: '' },
    { level: 1, label: 'Weak', class: 'strength-weak' },
    { level: 2, label: 'Fair', class: 'strength-fair' },
    { level: 3, label: 'Good', class: 'strength-good' },
    { level: 4, label: 'Strong', class: 'strength-strong' },
  ]
  return levels[score] || levels[0]
})

// Progress bar calculation
const progress = computed(() => {
  let filled = 0
  if (email.value.trim()) filled++
  if (password.value.length >= 8) filled++
  if (confirmPassword.value && confirmPassword.value === password.value) filled++
  if (agreedToTerms.value) filled++
  return (filled / 4) * 100
})

// Validation
const passwordsMatch = computed(() => {
  if (!confirmPassword.value) return true
  return password.value === confirmPassword.value
})

const isEmailFormatValid = computed(() => {
  if (!email.value.trim()) return true
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())
})

const isPhoneValid = computed(() => /^\d{7,15}$/.test(mobileNumber.value))

const isFormValid = computed(() => {
  return (
    name.value.trim() !== '' &&
    email.value.trim() !== '' &&
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim()) &&
    mobile.value !== '' &&
    isPhoneValid.value &&
    password.value.length >= 8 &&
    confirmPassword.value === password.value &&
    agreedToTerms.value
  )
})

// ── Step 1: Send OTP ──
const handleRegister = async () => {
  validationErrors.value = {}
  errorMessage.value = ''

  if (!name.value.trim()) {
    errorMessage.value = 'Please enter your name.'
    return
  }

  if (!email.value.trim()) {
    errorMessage.value = 'Please enter your email address.'
    return
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
    errorMessage.value = 'Please enter a valid email address (e.g. name@domain.com).'
    return
  }

  if (!mobile.value.trim() || !isPhoneValid.value) {
    errorMessage.value = 'Please enter a valid mobile number (7 to 15 digits).'
    return
  }

  if (password.value.length < 8) {
    errorMessage.value = 'Password must be at least 8 characters long.'
    return
  }

  if (password.value !== confirmPassword.value) {
    errorMessage.value = 'Passwords do not match.'
    return
  }

  if (!agreedToTerms.value) {
    errorMessage.value = 'Please agree to the Terms of Service and Privacy Policy.'
    return
  }

  isLoading.value = true

  try {
    const response = await auth.registerSendOtp({
      name: name.value,
      email: email.value.trim(),
      country_code: countryCode.value,
      mobile: mobileNumber.value,
      password: password.value,
    })

    auth.toastMessage(response?.message || 'OTP sent to your email.', { type: 'success' })
    startCountdown()
    currentStep.value = 'otp'
    await nextTick()
    focusOtpInput(0)
  } catch (err) {
    if(err.response?.status === 422) {
      validationErrors.value = err.response.data.errors || {}
    }
    errorMessage.value = err?.response?.data?.message || 'Registration failed. Please check the fields and try again.'
  } finally {
    isLoading.value = false
  }
}

// ── Step 2: OTP Verification ──
const otp = ref(['', '', '', ''])
const otpInputs = ref([])
const resendCooldown = ref(300)
let timerInterval = null

const formattedTimer = computed(() => {
  const mins = Math.floor(resendCooldown.value / 60).toString().padStart(2, '0')
  const secs = (resendCooldown.value % 60).toString().padStart(2, '0')
  return `${mins}:${secs}`
})

const maskedEmail = computed(() => {
  if (!email.value) return ''
  const [namepart, domain] = email.value.split('@')
  if (namepart.length <= 2) return email.value
  return `${namepart[0]}***${namepart[namepart.length - 1]}@${domain}`
})

const isOtpValid = computed(() => otp.value.every(digit => digit !== ''))

const handleOtpInput = (index, event) => {
  const value = event.target.value
  if (!/^\d*$/.test(value)) {
    otp.value[index] = ''
    return
  }
  if (value.length > 1) {
    otp.value[index] = value[0]
  }
  if (otp.value[index] !== '' && index < 3) {
    focusOtpInput(index + 1)
  }
  if (isOtpValid.value) {
    handleVerifyOtp()
  }
}

const handleOtpKeydown = (index, event) => {
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
  resendCooldown.value = 300
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
  otp.value = ['', '', '', '']

  try {
    await auth.registerResendOtp({ email: email.value })
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
    const response = await auth.registerVerifyOtp({
      email: email.value,
      otp: otpString,
    })

    auth.toastMessage(response?.message || 'Account created successfully!', { type: 'success' })
    await router.push({ name: auth.user?.is_onboarded ? 'Dashboard' : 'Onboarding' })
  } catch (err) {
    errorMessage.value = err?.response?.data?.message || 'Invalid OTP. Please try again.'
    otp.value = ['', '', '', '']
    focusOtpInput(0)
  } finally {
    isLoading.value = false
  }
}

const goBackToForm = () => {
  currentStep.value = 'form'
  errorMessage.value = ''
  otp.value = ['', '', '', '']
  if (timerInterval) clearInterval(timerInterval)
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">
      <!-- Brand (inline layout) -->
      <div class="auth-brand">
        <div class="auth-brand__icon">✦</div>
        <div class="auth-brand__text">
          <div class="auth-brand__name">
            <router-link to="/">
              Future Self
            </router-link>
          </div>
          <div class="auth-brand__tagline">Begin your transformation</div>
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

          <!-- Step 1: Registration Form -->
          <div v-if="currentStep === 'form'" key="step-form">
            <!-- Heading -->
            <div class="auth-heading">
              <h1 class="auth-heading__title">Create your account</h1>
              <p class="auth-heading__subtitle">Meet the version of your future self, that will help you achieve your goals</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleRegister" id="register-form">
              
              <!-- Name -->
              <div class="auth-field" :class="{ 'auth-field--error': validationErrors.name }">
                <label class="auth-field__label" for="register-name">Name</label>
                <div class="auth-field__wrapper">
                  <svg class="auth-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                  </svg>
                  <input
                    id="register-name"
                    v-model="name"
                    type="text"
                    class="auth-field__input"
                    placeholder="John Doe"
                    autocomplete="name"
                    required
                  />
                </div>
                <p v-if="validationErrors.name" class="auth-field__error-text">{{ Array.isArray(validationErrors.name) ? validationErrors.name[0] : validationErrors.name }}</p>
              </div>

              <!-- Email -->
              <div class="auth-field" :class="{ 'auth-field--error': (email.trim() !== '' && !isEmailFormatValid) || validationErrors.email }">
                <label class="auth-field__label" for="register-email">Email</label>
                <div class="auth-field__wrapper">
                  <svg class="auth-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="3"/>
                    <path d="M22 7l-8.97 5.7a1.94 1.94 0 01-2.06 0L2 7"/>
                  </svg>
                  <input
                    id="register-email"
                    v-model="email"
                    type="email"
                    class="auth-field__input"
                    placeholder="you@example.com"
                    autocomplete="email"
                    required
                  />
                </div>
                <p v-if="email.trim() !== '' && !isEmailFormatValid" class="auth-field__error-text">Please enter a valid email address (e.g. name@domain.com)</p>
                <p v-else-if="validationErrors.email" class="auth-field__error-text">{{ Array.isArray(validationErrors.email) ? validationErrors.email[0] : validationErrors.email }}</p>
              </div>
              
              <!-- Mobile -->
              <div class="auth-field" :class="{ 'auth-field--error': (mobile.trim() !== '' && !isPhoneValid) || validationErrors.mobile }">
                <label class="auth-field__label" for="register-mobile">Mobile</label>
                <div class="auth-field__wrapper auth-field__phone-row">
                  <div class="auth-field__country-wrapper" ref="countryMenuRef">
                    <button type="button" class="auth-field__country-button" @click="toggleCountryMenu" aria-label="Select country code">
                      <CountryFlag :country="selectedCountryIso2" size="small" rounded class="auth-field__country-flag" />
                      <span class="auth-field__country-code">
                        {{ selectedCountry.dialCode }}
                      </span>
                    </button>
                    <div v-if="countryMenuOpen" class="auth-field__country-dropdown">
                      <button
                        v-for="country in countryOptions"
                        :key="country.iso2"
                        type="button"
                        class="auth-field__country-item"
                        @click="selectCountry(country.iso2)"
                      >
                        <CountryFlag :country="country.iso2" size="small" rounded class="auth-field__country-flag" />
                        <span>{{ country.dialCode }}</span>
                      </button>
                    </div>
                  </div>
                  <input
                    id="register-mobile"
                    v-model="mobile"
                    type="tel"
                    class="auth-field__input auth-field__input--mobile"
                    placeholder="9876543210"
                    autocomplete="tel-national"
                    required
                  />
                </div>
                <p v-if="mobile.trim() !== '' && !isPhoneValid" class="auth-field__error-text">Please enter a valid phone number (7 to 15 digits)</p>
                <p v-else-if="validationErrors.mobile" class="auth-field__error-text">{{ Array.isArray(validationErrors.mobile) ? validationErrors.mobile[0] : validationErrors.mobile }}</p>
              </div>

              <!-- Password -->
              <div class="auth-field" :class="{ 'auth-field--error': validationErrors.password }">
                <label class="auth-field__label" for="register-password">Password</label>
                <div class="auth-field__wrapper">
                  <svg class="auth-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="3"/>
                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                  </svg>
                  <input
                    id="register-password"
                    v-model="password"
                    :type="showPassword ? 'text' : 'password'"
                    class="auth-field__input"
                    placeholder="Min. 8 characters"
                    autocomplete="new-password"
                    required
                    minlength="8"
                  />
                  <button type="button" class="auth-field__toggle" @click="showPassword = !showPassword" aria-label="Toggle password visibility">
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
                <!-- Password strength indicator -->
                <div v-if="password" class="password-strength">
                  <div
                    v-for="i in 4"
                    :key="i"
                    class="password-strength__segment"
                    :class="{
                      'password-strength__segment--active': i <= passwordStrength.level,
                      [passwordStrength.class]: i <= passwordStrength.level
                    }"
                  ></div>
                </div>
                <p v-if="validationErrors.password" class="auth-field__error-text">{{ Array.isArray(validationErrors.password) ? validationErrors.password[0] : validationErrors.password }}</p>
              </div>

              <!-- Confirm Password -->
              <div class="auth-field" :class="{ 'auth-field--error': !passwordsMatch }">
                <label class="auth-field__label" for="register-confirm-password">Confirm Password</label>
                <div class="auth-field__wrapper">
                  <svg class="auth-field__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 12l2 2 4-4"/>
                    <circle cx="12" cy="12" r="10"/>
                  </svg>
                  <input
                    id="register-confirm-password"
                    v-model="confirmPassword"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    class="auth-field__input"
                    placeholder="Re-enter password"
                    autocomplete="new-password"
                    required
                  />
                  <button type="button" class="auth-field__toggle" @click="showConfirmPassword = !showConfirmPassword" aria-label="Toggle confirm password visibility">
                    <svg v-if="!showConfirmPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                      <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                  </button>
                </div>
                <p v-if="!passwordsMatch" class="auth-field__error-text">Passwords do not match</p>
              </div>

              <!-- Terms -->
              <div class="auth-terms">
                <input
                  id="register-terms"
                  v-model="agreedToTerms"
                  type="checkbox"
                  class="auth-terms__checkbox"
                />
                <label for="register-terms" class="auth-terms__text">
                  I agree to the <a href="#" @click.prevent="openLegalModal('terms')">Terms of Service</a> and <a href="#" @click.prevent="openLegalModal('privacy')">Privacy Policy</a>. I understand this is an AI experience.
                </label>
              </div>

              <!-- Submit -->
              <button
                id="register-submit"
                type="submit"
                class="auth-submit"
                :class="{ 'auth-submit--loading': isLoading }"
                :disabled="isLoading"
              >
                <span v-if="isLoading" class="auth-submit__spinner"></span>
                {{ isLoading ? 'Sending OTP...' : 'Continue →' }}
              </button>
            </form>

            <!-- Divider -->
            <div class="auth-divider">
              <div class="auth-divider__line"></div>
              <span class="auth-divider__text">or sign up with</span>
              <div class="auth-divider__line"></div>
            </div>

            <!-- Social logins -->
            <div class="auth-socials">
              <button id="register-google" class="auth-social-btn" type="button" @click="handleGoogleLogin">
                <svg class="auth-social-btn__icon" viewBox="0 0 24 24">
                  <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                  <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                  <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                  <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Google
              </button>
            </div>

            <!-- Footer -->
            <div class="auth-footer">
              Already have an account? <router-link to="/login">Sign in</router-link>
            </div>
          </div>

          <!-- Step 2: OTP Verification -->
          <div v-else-if="currentStep === 'otp'" key="step-otp">
            <div class="auth-heading">
              <h1 class="auth-heading__title">Verify your email</h1>
              <p class="auth-heading__subtitle">
                We've sent a 4-digit code to
                <br>
                <span class="otp-email-badge">{{ maskedEmail }}</span>
              </p>
            </div>

            <form @submit.prevent="handleVerifyOtp" id="register-otp-form">
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
                  <span class="otp-timer" :class="{'otp-timer--warning': resendCooldown <= 30}">
                    {{ resendCooldown > 0 ? formattedTimer : 'Code expired' }}
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
                id="register-verify-submit"
                type="submit"
                class="auth-submit"
                :class="{ 'auth-submit--loading': isLoading }"
                :disabled="!isOtpValid || isLoading"
              >
                <span v-if="isLoading" class="auth-submit__spinner"></span>
                {{ isLoading ? 'Verifying...' : 'Verify & Create Account' }}
              </button>
            </form>

            <div class="auth-footer">
              <a href="#" @click.prevent="goBackToForm">← Back to registration</a>
            </div>
          </div>

        </Transition>
      </div>
    </div>

    <!-- Legal Modal -->
    <LegalModal
      :is-open="isLegalModalOpen"
      :initial-tab="legalModalTab"
      @close="isLegalModalOpen = false"
    />
  </div>
</template>

<style scoped>
/* Page-specific overrides — layout handled by auth.css */
</style>

