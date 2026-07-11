<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import CountryFlag from 'vue-country-flag-next'
import countryTelephoneData from 'country-telephone-data'
import authService from '@/services/authService'

const router = useRouter()
const auth = useAuthStore()
const selectedCountryIso2 = ref('in')
const countryMenuOpen = ref(false)
const countryMenuRef = ref(null)

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


// console.log(auth.user.created_at)

// Form state — pre-filled with static placeholder data for design purposes.
// Replace these with reactive bindings to auth.user when making it dynamic.
const form = ref({
  name: auth.user.name,
  email: auth.user.email,
  country_code: auth.user.country_code,
  mobile: auth.user.mobile
  // bio: 'Aspiring creator who believes in building things that matter. Passionate about AI, design, and self-improvement.',
  // location: 'Mumbai, India',
  // dateOfBirth: '2003-06-15',
  // gender: 'male',
})

const joiningDate = ref(auth.user.created_at)

const passwords = ref({
  current: '',
  new: '',
  confirm: '',
})

const showCurrentPw = ref(false)
const showNewPw = ref(false)
const showConfirmPw = ref(false)
const isSaving = ref(false)
const showSaved = ref(false)

// Computed
const userInitials = computed(() => {
  if (!form.value.name) return '?'
  return form.value.name
    .split(' ')
    .map(w => w[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const joinedDate = computed(() => {
  return new Date(joiningDate.value).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
})

const passwordsMatch = computed(() => {
  if (!passwords.value.new || !passwords.value.confirm) return true
  return passwords.value.new === passwords.value.confirm
})

// Methods
async function handleSaveProfile() {
  isSaving.value = true
  // TODO: Call your API to save profile
  try{
    form.value.country_code = countryCode.value;
    
    await authService.updateProfile(form.value)
    setTimeout(() => {
      isSaving.value = false
      showSaved.value = true
      auth.toastMessage('Profile updated successfully', { type: 'success' })
      setTimeout(() => { showSaved.value = false }, 2500)
    }, 800)
  }catch(err){
    if(err.response?.status === 422) {
      auth.toastMessage(err.response.data.errors, { type: 'error' })
    }else{
      auth.toastMessage(err.response?.data?.message || 'Something went wrong', { type: 'error' })
    }
  }
}

async function handleChangePassword() {
  if (!passwordsMatch.value) {
    auth.toastMessage('Passwords do not match', { type: 'error' })
    return
  }
  // TODO: Call your API to change password
  // e.g. await ProfileService.changePassword(passwords.value)
  auth.toastMessage('Password changed successfully', { type: 'success' })
  passwords.value = { current: '', new: '', confirm: '' }
}

function handleDeleteAccount() {
  // TODO: Implement with a confirmation modal
  auth.toastMessage('This action requires confirmation', { type: 'warning' })
}

function goBack() {
  router.back()
}
</script>

<template>
  <div class="profile-page">
    <!-- ── NAV ──────────────────────────────────── -->
    <nav class="profile-nav">
      <div class="profile-nav-left">
        <router-link to="/" class="profile-nav-logo">
          <div class="profile-nav-logo-icon">✨</div>
          <span class="profile-nav-logo-name">FutureYou</span>
        </router-link>
        <div class="profile-nav-divider"></div>
        <span class="profile-nav-title">Edit Profile</span>
      </div>

      <div class="profile-nav-actions">
        <button class="profile-nav-btn" @click="goBack" id="btn-back">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12" /><polyline points="12 19 5 12 12 5" />
          </svg>
          Back
        </button>
      </div>
    </nav>

    <!-- ── MAIN CONTENT ────────────────────────── -->
    <div class="profile-content">
      <div class="profile-wrapper">

        <!-- ── AVATAR / HEADER CARD ──────────── -->
        <div class="profile-header-card">
          <div class="profile-avatar-wrapper">
            <div class="profile-avatar">{{ userInitials }}</div>
            <div class="profile-avatar-ring"></div>
            <div class="profile-avatar-badge" title="Change photo" id="btn-change-avatar">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z" />
                <circle cx="12" cy="13" r="4" />
              </svg>
            </div>
          </div>
          <h2 class="profile-header-name">{{ form.name || 'Your Name' }}</h2>
          <p class="profile-header-email">{{ form.email }}</p>
          <div class="profile-header-badges">
            <span class="profile-badge profile-badge-onboarded">
              <span class="profile-badge-dot"></span>
              Onboarded
            </span>
            <span class="profile-badge profile-badge-member">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z" />
              </svg>
              Member since {{ joinedDate }}
            </span>
          </div>
        </div>

        <!-- ── PERSONAL INFO SECTION ─────────── -->
        <div class="profile-section">
          <div class="profile-section-header">
            <div class="profile-section-icon violet">👤</div>
            <div>
              <div class="profile-section-title">Personal Information</div>
              <div class="profile-section-desc">Update your basic details</div>
            </div>
          </div>

          <div class="profile-form-grid">
            <!-- Full Name -->
            <div class="profile-field">
              <label class="profile-field-label" for="profile-name">
                Full Name <span class="required">*</span>
              </label>
              <div class="profile-input-wrapper">
                <input
                  v-model="form.name"
                  type="text"
                  class="profile-input"
                  id="profile-name"
                  placeholder="Enter your full name"
                />
                <svg class="profile-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" /><circle cx="12" cy="7" r="4" />
                </svg>
              </div>
            </div>

            <!-- Email -->
            <div class="profile-field">
              <label class="profile-field-label" for="profile-email">
                Email Address <span class="required">*</span>
              </label>
              <div class="profile-input-wrapper">
                <input
                  v-model="form.email"
                  type="email"
                  class="profile-input"
                  id="profile-email"
                  placeholder="you@example.com"
                />
                <svg class="profile-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="2" y="4" width="20" height="16" rx="3" /><path d="M22 7l-8.97 5.7a1.94 1.94 0 01-2.06 0L2 7" />
                </svg>
              </div>
            </div>

            <div class="auth-field">
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
              v-model="form.mobile"
              type="tel"
              class="auth-field__input auth-field__input--mobile"
              placeholder="9876543210"
              autocomplete="tel-national"
              required
            />
          </div>
        </div>

            <!-- Phone -->
            <!-- <div class="profile-field">
              <label class="profile-field-label" for="profile-phone">Phone</label>
              <div class="profile-input-wrapper">
                <input
                  v-model="form.phone"
                  type="tel"
                  class="profile-input"
                  id="profile-phone"
                  placeholder="XXXXX XXXXX"
                />
                <svg class="profile-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="5" y="2" width="14" height="20" rx="2" ry="2" /><line x1="12" y1="18" x2="12.01" y2="18" />
                </svg>
              </div>
            </div> -->

            <!-- Location -->
            <!-- <div class="profile-field">
              <label class="profile-field-label" for="profile-location">Location</label>
              <div class="profile-input-wrapper">
                <input
                  v-model="form.location"
                  type="text"
                  class="profile-input"
                  id="profile-location"
                  placeholder="City, Country"
                />
                <svg class="profile-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" /><circle cx="12" cy="10" r="3" />
                </svg>
              </div>
            </div> -->

            <!-- Date of Birth -->
            <!-- <div class="profile-field">
              <label class="profile-field-label" for="profile-dob">Date of Birth</label>
              <div class="profile-input-wrapper">
                <input
                  v-model="form.dateOfBirth"
                  type="date"
                  class="profile-input"
                  id="profile-dob"
                />
                <svg class="profile-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="3" y1="10" x2="21" y2="10" />
                </svg>
              </div>
            </div> -->

            <!-- Gender -->
            <!-- <div class="profile-field">
              <label class="profile-field-label" for="profile-gender">Gender</label>
              <select v-model="form.gender" class="profile-select" id="profile-gender">
                <option value="" disabled>Select gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="non-binary">Non-Binary</option>
                <option value="prefer-not">Prefer not to say</option>
              </select>
            </div> -->

            <!-- Bio -->
            <!-- <div class="profile-field field-full">
              <label class="profile-field-label" for="profile-bio">Bio</label>
              <textarea
                v-model="form.bio"
                class="profile-textarea"
                id="profile-bio"
                rows="3"
                placeholder="Tell your future self a little about who you are today..."
              ></textarea>
            </div> -->
          </div>

          <!-- Actions -->
          <div class="profile-actions">
            <div class="profile-saved-indicator" :class="{ visible: showSaved }">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>
              Saved!
            </div>
            <button class="profile-btn profile-btn-ghost" @click="goBack" id="btn-cancel-profile">
              Cancel
            </button>
            <button
              class="profile-btn profile-btn-primary"
              @click="handleSaveProfile"
              :disabled="isSaving"
              id="btn-save-profile"
            >
              <span v-if="isSaving" class="profile-btn-spinner"></span>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                <polyline points="17 21 17 13 7 13 7 21" /><polyline points="7 3 7 8 15 8" />
              </svg>
              {{ isSaving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </div>

        <!-- ── CHANGE PASSWORD SECTION ───────── -->
        <div class="profile-section">
          <div class="profile-section-header">
            <div class="profile-section-icon pink">🔒</div>
            <div>
              <div class="profile-section-title">Change Password</div>
              <div class="profile-section-desc">Secure your account with a new password</div>
            </div>
          </div>

          <div class="profile-form-grid">
            <!-- Current Password -->
            <div class="profile-field field-full">
              <label class="profile-field-label" for="profile-current-pw">Current Password</label>
              <div class="profile-input-wrapper">
                <input
                  v-model="passwords.current"
                  :type="showCurrentPw ? 'text' : 'password'"
                  class="profile-input"
                  id="profile-current-pw"
                  placeholder="Enter current password"
                />
                <svg class="profile-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0110 0v4" />
                </svg>
                <button
                  class="profile-password-toggle"
                  type="button"
                  @click="showCurrentPw = !showCurrentPw"
                  id="btn-toggle-current-pw"
                >
                  <svg v-if="!showCurrentPw" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" />
                    <line x1="1" y1="1" x2="23" y2="23" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- New Password -->
            <div class="profile-field">
              <label class="profile-field-label" for="profile-new-pw">New Password</label>
              <div class="profile-input-wrapper">
                <input
                  v-model="passwords.new"
                  :type="showNewPw ? 'text' : 'password'"
                  class="profile-input"
                  id="profile-new-pw"
                  placeholder="Enter new password"
                />
                <svg class="profile-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0110 0v4" />
                </svg>
                <button
                  class="profile-password-toggle"
                  type="button"
                  @click="showNewPw = !showNewPw"
                  id="btn-toggle-new-pw"
                >
                  <svg v-if="!showNewPw" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" />
                    <line x1="1" y1="1" x2="23" y2="23" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Confirm Password -->
            <div class="profile-field">
              <label class="profile-field-label" for="profile-confirm-pw">
                Confirm Password
                <span v-if="!passwordsMatch" style="color: #f87171; font-weight: 500; text-transform: none; letter-spacing: 0;">
                  — doesn't match
                </span>
              </label>
              <div class="profile-input-wrapper">
                <input
                  v-model="passwords.confirm"
                  :type="showConfirmPw ? 'text' : 'password'"
                  class="profile-input"
                  :style="!passwordsMatch ? 'border-color: rgba(248, 113, 113, 0.4)' : ''"
                  id="profile-confirm-pw"
                  placeholder="Re-enter new password"
                />
                <svg class="profile-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0110 0v4" />
                </svg>
                <button
                  class="profile-password-toggle"
                  type="button"
                  @click="showConfirmPw = !showConfirmPw"
                  id="btn-toggle-confirm-pw"
                >
                  <svg v-if="!showConfirmPw" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" />
                    <line x1="1" y1="1" x2="23" y2="23" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="profile-actions">
            <button
              class="profile-btn profile-btn-primary"
              @click="handleChangePassword"
              :disabled="!passwords.current || !passwords.new || !passwordsMatch"
              id="btn-change-password"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0110 0v4" />
              </svg>
              Update Password
            </button>
          </div>
        </div>

        <!-- ── DANGER ZONE ───────────────────── -->
        <div class="profile-danger-section">
          <div class="profile-danger-header">
            <div class="profile-danger-icon">⚠️</div>
            <div>
              <div class="profile-danger-title">Danger Zone</div>
              <div class="profile-danger-desc">Irreversible and destructive actions</div>
            </div>
          </div>

          <div class="profile-danger-items">
            <div class="profile-danger-item">
              <div class="profile-danger-item-info">
                <h4>Delete your account</h4>
                <p>Once deleted, all of your data — conversations, goals, and profile — will be permanently removed.</p>
              </div>
              <button
                class="profile-btn profile-btn-danger"
                @click="handleDeleteAccount"
                id="btn-delete-account"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="3 6 5 6 21 6" /><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" /><path d="M10 11v6" /><path d="M14 11v6" /><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" />
                </svg>
                Delete Account
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
/* All styles are in profile.css */
</style>
