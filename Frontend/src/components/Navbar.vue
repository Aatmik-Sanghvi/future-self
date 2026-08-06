<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()
const dropdownOpen = ref(false)
const dropdownRef = ref(null)
const menuOpen = ref(false)

const userInitials = computed(() => {
  if (!auth.user?.name) return '?'
  return auth.user.name
    .split(' ')
    .map(w => w[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

function toggleDropdown() {
  dropdownOpen.value = !dropdownOpen.value
}

function closeDropdown(e) {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    dropdownOpen.value = false
  }
}

function toggleMobileMenu() {
  menuOpen.value = !menuOpen.value
}

function closeMobileMenu() {
  menuOpen.value = false
}

// Lock body scroll when mobile menu is open
watch(menuOpen, (val) => {
  document.body.style.overflow = val ? 'hidden' : ''
})

async function handleLogout() {
  dropdownOpen.value = false
  closeMobileMenu()
  await auth.logout()
  router.push({ name: 'Login' })
}

function navigateTo(routePath) {
  dropdownOpen.value = false
  closeMobileMenu()
  router.push(routePath)
}

onMounted(() => {
  document.addEventListener('click', closeDropdown)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeDropdown)
  document.body.style.overflow = ''
})
</script>

<template>
  <nav>
    <div class="nav-logo">
      <router-link to="/" class="nav-logo-link">
        <div class="nav-logo-icon">✨</div>
        <span class="nav-logo-name">Future Self</span>
      </router-link>
    </div>

    <div class="nav-links">
      <a href="#features">Features</a>
      <a href="#how">How It Works</a>
      <a href="#testimonials">Testimonials</a>
      <a href="#pricing">Pricing</a>
      <router-link v-if="auth.isAuthenticated" to="/chat" class="nav-chat-btn">Chat</router-link>
    </div>

    <!-- Unauthenticated actions -->
    <div class="nav-actions" v-if="!auth.isAuthenticated">
      <router-link to="/login" class="btn-secondary">Login</router-link>
      <router-link to="/register" class="btn-primary">Start Free</router-link>

      <!-- Mobile hamburger -->
      <button class="nav-mobile-toggle" @click="toggleMobileMenu" type="button" aria-label="Open menu">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
    </div>

    <!-- Authenticated: user dropdown -->
    <div class="nav-actions" v-else>
      <!-- Mobile hamburger (authenticated) -->
      <button class="nav-mobile-toggle" @click="toggleMobileMenu" type="button" aria-label="Open menu">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>

      <div class="nav-user-dropdown" ref="dropdownRef">
        <button
          class="nav-user-avatar"
          @click="toggleDropdown"
          id="nav-user-avatar"
          type="button"
        >
          <img v-if="auth.user.profile_image" :src="auth.user.profile_image" class="profile-image"> 
          <span v-else>{{ userInitials }}</span>
        </button>

        <Transition name="dropdown-fade">
          <div v-if="dropdownOpen" class="nav-dropdown-menu" id="nav-dropdown-menu">
            <div class="nav-dropdown-header">
              <img v-if="auth.user.profile_image" :src="auth.user.profile_image" class="profile-image"> 
              <div class="nav-dropdown-user-avatar" v-else>{{ userInitials }}</div>
              <div class="nav-dropdown-user-info">
                <div class="nav-dropdown-user-name">{{ auth.user?.name || 'User' }}</div>
                <div class="nav-dropdown-user-email">{{ auth.user?.email || '' }}</div>
              </div>
            </div>

            <div class="nav-dropdown-divider"></div>

            <button class="nav-dropdown-item" @click="navigateTo('/edit-profile')" id="dropdown-profile">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
              </svg>
              Profile
            </button>

            <button class="nav-dropdown-item" @click="navigateTo('/onboarding')" id="dropdown-onboarding">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
              </svg>
              Onboarding Steps
            </button>

            <div class="nav-dropdown-divider"></div>

            <button class="nav-dropdown-item nav-dropdown-item-danger" @click="handleLogout" id="dropdown-logout">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
              </svg>
              Logout
            </button>
          </div>
        </Transition>
      </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div
      class="nav-mobile-overlay"
      :class="{ show: menuOpen }"
      @click="closeMobileMenu"
    ></div>

    <!-- Mobile Slide-in Menu -->
    <div class="nav-mobile-menu" :class="{ open: menuOpen }">
      <button class="nav-mobile-close" @click="closeMobileMenu" type="button" aria-label="Close menu">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>

      <a href="#features" class="nav-mobile-link" @click="closeMobileMenu">Features</a>
      <a href="#how" class="nav-mobile-link" @click="closeMobileMenu">How It Works</a>
      <a href="#testimonials" class="nav-mobile-link" @click="closeMobileMenu">Testimonials</a>
      <a href="#pricing" class="nav-mobile-link" @click="closeMobileMenu">Pricing</a>

      <template v-if="auth.isAuthenticated">
        <router-link to="/chat" class="nav-mobile-link" @click="closeMobileMenu">Chat</router-link>
        <div class="nav-mobile-divider"></div>
      </template>

      <template v-else>
        <div class="nav-mobile-divider"></div>
        <div class="nav-mobile-actions">
          <router-link to="/login" class="btn-mobile-login" @click="closeMobileMenu">Login</router-link>
          <router-link to="/register" class="btn-mobile-primary" @click="closeMobileMenu">Start Free</router-link>
        </div>
      </template>
    </div>
  </nav>
</template>

<style scoped>

</style>

