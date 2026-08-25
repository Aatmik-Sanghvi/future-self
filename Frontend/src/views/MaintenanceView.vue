<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const isChecking = ref(false)
const checkMessage = ref('')
const countdown = ref(15)
let timer = null

const API_BASE = import.meta.env.VITE_API_URL || ''

async function checkBackendStatus() {
  isChecking.value = true
  checkMessage.value = 'Checking system status...'
  
  try {
    const res = await axios.get(`${API_BASE}/stats`, {
      timeout: 5000,
      validateStatus: (status) => status < 500, // any non-5xx means backend is up
    })

    if (res.status !== 503) {
      checkMessage.value = 'Systems are back online! Redirecting...'
      setTimeout(() => {
        router.push('/')
      }, 1000)
    } else {
      checkMessage.value = 'Maintenance is still ongoing. Retrying automatically...'
      countdown.value = 15
    }
  } catch (err) {
    if (err.response && err.response.status !== 503) {
      // Backend responded (e.g. 404, 401, 200) -> meaning maintenance is off!
      checkMessage.value = 'Systems are back online! Redirecting...'
      setTimeout(() => {
        router.push('/')
      }, 1000)
    } else {
      checkMessage.value = 'Maintenance is still in progress.'
      countdown.value = 15
    }
  } finally {
    isChecking.value = false
  }
}

onMounted(() => {
  timer = setInterval(() => {
    if (countdown.value > 1) {
      countdown.value--
    } else {
      countdown.value = 15
      checkBackendStatus()
    }
  }, 1000)
})

onBeforeUnmount(() => {
  if (timer) clearInterval(timer)
})
</script>

<template>
  <div class="maintenance-container">
    <!-- Ambient background glow elements -->
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>
    <div class="glow-orb orb-3"></div>

    <div class="maintenance-card">
      <!-- Icon with glowing aura animation -->
      <div class="maintenance-icon-wrapper">
        <div class="pulse-ring"></div>
        <div class="maintenance-icon">🛠️</div>
      </div>

      <!-- Badge -->
      <div class="maintenance-badge">
        <span class="badge-dot"></span>
        <span>Scheduled Maintenance</span>
      </div>

      <!-- Title & description -->
      <h1 class="maintenance-title">We'll Be Back Soon</h1>
      <p class="maintenance-subtitle">
        FutureSelf is currently undergoing planned system upgrades and maintenance to make your experience even better.
      </p>

      <!-- Status indicator card -->
      <div class="status-box">
        <div class="status-row">
          <div class="status-indicator">
            <span class="status-live-dot"></span>
            <span class="status-text">Upgrading Core Systems</span>
          </div>
          <span class="status-auto-text">Auto-checking in <strong>{{ countdown }}s</strong></span>
        </div>
        <div v-if="checkMessage" class="status-feedback">{{ checkMessage }}</div>
      </div>

      <!-- Manual Retry Action -->
      <div class="maintenance-actions">
        <button
          class="btn-retry"
          :disabled="isChecking"
          @click="checkBackendStatus"
          id="btn-retry-status"
        >
          <svg
            v-if="!isChecking"
            xmlns="http://www.w3.org/2000/svg"
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <polyline points="23 4 23 10 17 10" />
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
          </svg>
          <span v-else class="spinner"></span>
          <span>{{ isChecking ? 'Checking...' : 'Check Again Now' }}</span>
        </button>
      </div>

      <div class="maintenance-footer">
        <span>✨ FutureSelf AI Platform</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.maintenance-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0d0b18;
  color: #ffffff;
  padding: 24px 16px;
  position: relative;
  overflow: hidden;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

/* ── Ambient Background Orbs ──────────────────────── */
.glow-orb {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(90px);
  opacity: 0.25;
}

.orb-1 {
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, #7c3aed 0%, transparent 70%);
  top: -100px;
  left: 20%;
  animation: floatOrb 8s ease-in-out infinite alternate;
}

.orb-2 {
  width: 350px;
  height: 350px;
  background: radial-gradient(circle, #ff7a18 0%, transparent 70%);
  bottom: -80px;
  right: 15%;
  animation: floatOrb 10s ease-in-out infinite alternate-reverse;
}

.orb-3 {
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, #4f46e5 0%, transparent 70%);
  bottom: 20%;
  left: 10%;
}

/* ── Card ────────────────────────────────────────── */
.maintenance-card {
  position: relative;
  z-index: 10;
  max-width: 540px;
  width: 100%;
  padding: 48px 36px 36px;
  background: rgba(22, 18, 38, 0.75);
  border: 1px solid rgba(167, 139, 250, 0.16);
  border-radius: 28px;
  box-shadow:
    0 24px 80px rgba(0, 0, 0, 0.6),
    inset 0 1px 0 rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  text-align: center;
  animation: cardSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

/* ── Icon & Aura ─────────────────────────────────── */
.maintenance-icon-wrapper {
  position: relative;
  width: 80px;
  height: 80px;
  margin: 0 auto 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.maintenance-icon {
  font-size: 2.8rem;
  line-height: 1;
  z-index: 2;
  filter: drop-shadow(0 8px 24px rgba(124, 58, 237, 0.6));
  animation: wrenchWiggle 3s ease-in-out infinite;
}

.pulse-ring {
  position: absolute;
  inset: -8px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(124, 58, 237, 0.35) 0%, transparent 70%);
  animation: pulseAura 2.5s ease-in-out infinite;
  z-index: 1;
}

/* ── Badge ───────────────────────────────────────── */
.maintenance-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 16px;
  border-radius: 9999px;
  background: rgba(255, 170, 0, 0.12);
  border: 1px solid rgba(255, 170, 0, 0.3);
  color: #ffaa00;
  font-size: 0.8rem;
  font-weight: 600;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  margin-bottom: 20px;
}

.badge-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #ffaa00;
  box-shadow: 0 0 8px #ffaa00;
  animation: blink 1.5s infinite;
}

/* ── Title & Subtitle ────────────────────────────── */
.maintenance-title {
  font-size: 2.2rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  line-height: 1.2;
  margin: 0 0 14px;
  background: linear-gradient(135deg, #ffffff 0%, #c4b5fd 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.maintenance-subtitle {
  font-size: 1rem;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.65);
  margin: 0 0 32px;
}

/* ── Status Box ──────────────────────────────────── */
.status-box {
  background: rgba(10, 8, 20, 0.6);
  border: 1px solid rgba(167, 139, 250, 0.12);
  border-radius: 16px;
  padding: 14px 18px;
  margin-bottom: 28px;
  text-align: left;
}

.status-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
}

.status-live-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #38ef7d;
  box-shadow: 0 0 10px #38ef7d;
  animation: pulseLive 1.8s infinite;
}

.status-text {
  font-size: 0.85rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
}

.status-auto-text {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.45);
}

.status-auto-text strong {
  color: #c4b5fd;
}

.status-feedback {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  font-size: 0.8rem;
  color: #c4b5fd;
  text-align: center;
}

/* ── Retry Button ────────────────────────────────── */
.maintenance-actions {
  margin-bottom: 24px;
}

.btn-retry {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  width: 100%;
  padding: 14px 24px;
  border-radius: 14px;
  background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
  border: 1px solid rgba(167, 139, 250, 0.35);
  color: #ffffff;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  box-shadow:
    0 8px 24px rgba(124, 58, 237, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.2);
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-retry:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow:
    0 12px 32px rgba(124, 58, 237, 0.5),
    inset 0 1px 0 rgba(255, 255, 255, 0.3);
  border-color: rgba(167, 139, 250, 0.6);
}

.btn-retry:active:not(:disabled) {
  transform: translateY(0);
}

.btn-retry:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

/* ── Spinner ─────────────────────────────────────── */
.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

/* ── Footer ──────────────────────────────────────── */
.maintenance-footer {
  font-size: 0.78rem;
  color: rgba(255, 255, 255, 0.35);
}

/* ── Keyframes ───────────────────────────────────── */
@keyframes floatOrb {
  0% { transform: translate(0, 0); }
  100% { transform: translate(30px, 40px); }
}

@keyframes pulseAura {
  0%, 100% { transform: scale(0.9); opacity: 0.4; }
  50% { transform: scale(1.3); opacity: 0.85; }
}

@keyframes wrenchWiggle {
  0%, 100% { transform: rotate(0deg); }
  15% { transform: rotate(-10deg); }
  30% { transform: rotate(12deg); }
  45% { transform: rotate(-8deg); }
  60% { transform: rotate(4deg); }
  75% { transform: rotate(0deg); }
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}

@keyframes pulseLive {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.3); opacity: 0.6; }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes cardSlideUp {
  from {
    opacity: 0;
    transform: translateY(24px) scale(0.97);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@media (max-width: 520px) {
  .maintenance-card {
    padding: 36px 20px 28px;
    border-radius: 22px;
  }
  .maintenance-title {
    font-size: 1.75rem;
  }
  .status-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }
}
</style>
