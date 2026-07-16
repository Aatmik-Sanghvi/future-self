<script setup>
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

defineProps({
  register: {
    type: Function,
    required: true,
  },
})
</script>

<template>
  <section class="hero">
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>
    <div class="hero-orb hero-orb-3"></div>

    <div class="hero-badge">
      <span class="hero-badge-dot">●</span>
      Now in Early Access — Join 12,000+ users
    </div>

    <h1 class="hero-h1">
      Meet the version of you<br />
      <span class="grad-text">5 years from now.</span>
    </h1>

    <p class="hero-sub">
      An AI that knows your goals, fears, and dreams — and talks to you like the
      future version of yourself who already made it.
    </p>

    <div v-if="!auth.isAuthenticated">
      <div class="hero-form">
        <!-- <input class="hero-input" type="email" placeholder="Enter your email" /> -->
        <button @click="register" class="btn-hero">Start Free</button>
      </div>
      <p class="hero-disclaimer">
        No credit card required · Free plan available · Cancel anytime
      </p>
    </div>

    <!-- Chat preview -->
    <div class="chat-preview">
      <div class="chat-header">
        <div class="chat-avatar">✨</div>
        <div>
          <div class="chat-name">Future You</div>
          <div class="chat-status">
            <div class="chat-status-dot"></div>
            <span class="chat-status-text">5 years ahead of you</span>
          </div>
        </div>
      </div>
      <div class="chat-messages">
        <div class="chat-msg-user">
          <div class="bubble">I feel like I'm not making progress.</div>
        </div>
        <div class="chat-msg-ai">
          <div class="avatar-sm">✨</div>
          <div class="bubble">
            I felt exactly the same at your stage. Here's what you're missing —
            progress isn't always visible. You've been building the foundation.
            <span class="bubble-accent">
              What's one thing you've improved at this month?</span
            >
          </div>
        </div>
        <div class="chat-prompts">
          <span class="chat-prompt">I feel lost</span>
          <span class="chat-prompt">Motivate me</span>
          <span class="chat-prompt">Calm my anxiety</span>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped></style>
