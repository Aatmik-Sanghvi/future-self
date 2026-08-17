<script setup>
  import { ref } from 'vue'
  import GeneralService from '@/services/GeneralService'
  
  const users = defineModel()
  const rating = ref(0)
  const messages = ref(0)
  const recommend = ref(0)

  const getStatsDetail = async () => {
    try {
      const res = await GeneralService.getStats()
      users.value = res.data.data.users_count
      rating.value = res.data.data.app_rating
      messages.value = res.data.data.messages_count
      recommend.value = res.data.data.recommend_to_other
    } catch (err) {
      console.error('Failed to load stats:', err)
    }
  }

  getStatsDetail();
</script>

<template>
  <div class="stats-bar">
    <div class="stat-item">
      <div class="stat-val">{{ users }}+</div>
      <div class="stat-label">Users</div>
    </div>
    <div class="stat-item">
      <div class="stat-val">{{ rating }}★</div>
      <div class="stat-label">Rating</div>
    </div>
    <div class="stat-item">
      <div class="stat-val">{{ messages }}+</div>
      <div class="stat-label">Messages Sent</div>
    </div>
    <div class="stat-item">
      <div class="stat-val">{{ recommend }}%</div>
      <div class="stat-label">Will recommend to other</div>
    </div>
  </div>
</template>

<style scoped></style>
