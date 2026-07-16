<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()
const dropdownOpen = ref(false)
const dropdownRef = ref(null)

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

async function handleLogout() {
  dropdownOpen.value = false
  await auth.logout()
  router.push({ name: 'Login' })
}

function navigateTo(routePath) {
  dropdownOpen.value = false
  router.push(routePath)
}

onMounted(() => {
  document.addEventListener('click', closeDropdown)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeDropdown)
})
</script>

<template>
  <nav>
    <div class="nav-logo">
      <router-link to="/" class="nav-logo-link">
        <div class="nav-logo-icon">✨</div>
        <span class="nav-logo-name">FutureYou</span>
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
    </div>

    <!-- Authenticated: user dropdown -->
    <div class="nav-actions" v-else>
      <div class="nav-user-dropdown" ref="dropdownRef">
        <button
          class="nav-user-avatar"
          @click="toggleDropdown"
          id="nav-user-avatar"
          type="button"
        >
          <img v-if="auth.user.profile_image" :src="'/storage/' + auth.user.profile_image" class="w-8 h-8 rounded-full object-cover"> 
          <span v-else>{{ userInitials }}</span>
        </button>

        <Transition name="dropdown-fade">
          <div v-if="dropdownOpen" class="nav-dropdown-menu" id="nav-dropdown-menu">
            <div class="nav-dropdown-header">
              <img v-if="auth.user.profile_image" :src="'/storage/' + auth.user.profile_image" class="w-8 h-8 rounded-full object-cover"> 
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
  </nav>
</template>

<style scoped>

</style>
