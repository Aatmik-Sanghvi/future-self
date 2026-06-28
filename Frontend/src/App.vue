<script setup>
  import { onMounted } from 'vue'
  import ToastContainer from 'vue3-toastify'
  import { useAuthStore } from '@/stores/auth'

  const auth = useAuthStore()

  onMounted(async () => {
    await auth.checkAuth()
  })
</script>

<template>
  <router-view v-slot="{ Component }">
    <transition name="auth-fade" mode="out-in">
      <component :is="Component" />
    </transition>
  </router-view>
  <ToastContainer />
</template>

<style scoped>
/* Global app-level styling handled by auth.css */
</style>
