<script setup>
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore()
</script>

<template>
  <nav>
    <div class="nav-logo">
      <div class="nav-logo-icon">✨</div>
      <span class="nav-logo-name">FutureYou</span>
    </div>
    <div class="nav-links">
      <a href="#features">Features</a>
      <a href="#how">How It Works</a>
      <a href="#testimonials">Testimonials</a>
      <a href="#pricing">Pricing</a>
    </div>
    <div class="nav-actions" v-if="!auth.isAuthenticated">
      <router-link to="/login" class="btn-secondary">Login</router-link>
      <router-link to="/register" class="btn-primary">Start Free</router-link>
    </div>
    <div class="nav-actions" v-else>
      <button @click="auth.logout()" class="logout-btn" type="button">Logout</button>
    </div>
  </nav>
</template>

<style scoped>

</style>
