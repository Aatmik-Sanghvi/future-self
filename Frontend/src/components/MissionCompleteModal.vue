<script setup>
import { ref } from 'vue'

const props = defineProps({
  mission: {
    type: Object,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['confirm', 'close'])

const reflection = ref('')

function handleConfirm() {
  emit('confirm', {
    reflection: reflection.value.trim(),
    confirmed_honest: true,
  })
}
</script>

<template>
  <Teleport to="body">
    <div class="modal-overlay" id="mission-complete-modal-overlay" @click.self="emit('close')">
      <div class="modal-card" role="dialog" aria-modal="true">
        <!-- Close Button -->
        <button class="modal-close-btn" @click="emit('close')" aria-label="Close modal">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>

        <!-- Ambient Glow -->
        <div class="modal-glow"></div>

        <!-- Header Icon & Badge -->
        <div class="modal-icon-wrapper">
          <span class="modal-icon">🛡️</span>
          <div class="modal-pulse"></div>
        </div>

        <div class="honesty-tag">
          <span class="honesty-sparkle">✨</span>
          <span>Honesty Check from Future You</span>
        </div>

        <!-- Title -->
        <h3 class="modal-title">Did you truly finish this mission?</h3>

        <!-- Mission snippet reminder -->
        <div class="mission-reminder-card">
          <div class="reminder-label">Today's Mission</div>
          <div class="reminder-title">{{ mission.title }}</div>
          <div class="reminder-time" v-if="mission.estimated_minutes">⏱️ {{ mission.estimated_minutes }} mins invested</div>
        </div>

        <!-- Psychological Prompt / Integrity Callout -->
        <div class="integrity-quote">
          <p class="quote-text">
            <strong>Don’t cheat on the person you’re becoming.</strong> Real growth happens in the genuine effort, not just checking off a box.
          </p>
          <p class="quote-subtext">
            If you did the honest work today, take pride in this win. If you still have a few minutes left, finish strong before marking it done!
          </p>
        </div>

        <!-- Reflection Input -->
        <div class="reflection-group">
          <label class="reflection-label" for="mission-reflection">
            <span>Quick Reflection</span>
            <span class="optional-pill">Optional</span>
          </label>
          <textarea
            id="mission-reflection"
            v-model="reflection"
            rows="2"
            class="reflection-input"
            placeholder="What did you learn or feel completing this? Write a quick note to your future self..."
            maxlength="600"
          ></textarea>
        </div>

        <!-- Actions -->
        <div class="modal-actions">
          <button
            type="button"
            class="btn-honest-confirm"
            id="btn-confirm-mission-complete"
            :disabled="loading"
            @click="handleConfirm"
          >
            <span v-if="loading" class="spinner"></span>
            <span v-else>🔥 I Did The Real Work — Mark Done</span>
          </button>

          <button
            type="button"
            class="btn-not-yet"
            id="btn-cancel-mission-complete"
            :disabled="loading"
            @click="emit('close')"
          >
            Not yet, I'll finish it first
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
/* ── Overlay ─────────────────────────────── */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(8, 8, 16, 0.75);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  padding: 16px;
  animation: fadeIn 0.25s ease-out;
}

/* ── Modal Card ──────────────────────────── */
.modal-card {
  position: relative;
  width: 100%;
  max-width: 500px;
  padding: 34px 28px 28px;
  border-radius: 24px;
  background: linear-gradient(150deg, #1c1c2e 0%, #151522 60%, #10101b 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow:
    0 24px 70px rgba(0, 0, 0, 0.6),
    0 0 0 1px rgba(255, 255, 255, 0.05) inset;
  text-align: center;
  overflow: hidden;
  animation: slideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

/* ── Ambient Glow ────────────────────────── */
.modal-glow {
  position: absolute;
  top: -80px;
  left: 50%;
  transform: translateX(-50%);
  width: 260px;
  height: 260px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(147, 51, 234, 0.25) 0%, rgba(249, 115, 22, 0.12) 50%, transparent 75%);
  pointer-events: none;
}

/* ── Close Button ────────────────────────── */
.modal-close-btn {
  position: absolute;
  top: 14px;
  right: 14px;
  width: 32px;
  height: 32px;
  border-radius: 10px;
  border: none;
  background: rgba(255, 255, 255, 0.06);
  color: rgba(255, 255, 255, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  z-index: 2;
}

.modal-close-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
  transform: scale(1.08);
}

/* ── Icon & Badge ────────────────────────── */
.modal-icon-wrapper {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 64px;
  border-radius: 20px;
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.18) 0%, rgba(147, 51, 234, 0.22) 100%);
  border: 1px solid rgba(249, 115, 22, 0.35);
  margin-bottom: 12px;
}

.modal-icon {
  font-size: 2rem;
}

.modal-pulse {
  position: absolute;
  inset: -6px;
  border-radius: 24px;
  border: 1px solid rgba(249, 115, 22, 0.3);
  animation: pulseRing 2.2s infinite;
}

.honesty-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 999px;
  background: rgba(249, 115, 22, 0.12);
  border: 1px solid rgba(249, 115, 22, 0.3);
  font-size: 0.78rem;
  font-weight: 600;
  color: #ffaa5b;
  margin-bottom: 10px;
}

/* ── Title ───────────────────────────────── */
.modal-title {
  font-size: 1.35rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 16px;
  letter-spacing: -0.02em;
}

/* ── Mission Reminder ────────────────────── */
.mission-reminder-card {
  padding: 12px 16px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  margin-bottom: 16px;
  text-align: left;
}

.reminder-label {
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: rgba(255, 255, 255, 0.45);
  margin-bottom: 2px;
}

.reminder-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: #f1f5f9;
  line-height: 1.3;
}

.reminder-time {
  font-size: 0.78rem;
  color: #a78bfa;
  margin-top: 4px;
}

/* ── Integrity Callout ───────────────────── */
.integrity-quote {
  padding: 14px 16px;
  border-radius: 14px;
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(249, 115, 22, 0.08) 100%);
  border: 1px solid rgba(249, 115, 22, 0.2);
  margin-bottom: 18px;
  text-align: left;
}

.quote-text {
  font-size: 0.88rem;
  line-height: 1.45;
  color: #fef08a;
  margin: 0 0 6px;
}

.quote-subtext {
  font-size: 0.8rem;
  line-height: 1.4;
  color: rgba(255, 255, 255, 0.65);
  margin: 0;
}

/* ── Reflection ──────────────────────────── */
.reflection-group {
  text-align: left;
  margin-bottom: 20px;
}

.reflection-label {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.8rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 6px;
}

.optional-pill {
  font-size: 0.68rem;
  padding: 2px 6px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.5);
}

.reflection-input {
  width: 100%;
  padding: 10px 12px;
  border-radius: 12px;
  background: rgba(0, 0, 0, 0.35);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #ffffff;
  font-size: 0.86rem;
  font-family: inherit;
  resize: vertical;
  min-height: 56px;
  outline: none;
  transition: all 0.2s ease;
}

.reflection-input:focus {
  border-color: #a855f7;
  box-shadow: 0 0 0 2px rgba(168, 85, 247, 0.2);
}

.reflection-input::placeholder {
  color: rgba(255, 255, 255, 0.3);
}

/* ── Actions ─────────────────────────────── */
.modal-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.btn-honest-confirm {
  width: 100%;
  padding: 13px 20px;
  border-radius: 14px;
  border: none;
  background: linear-gradient(135deg, #ea580c 0%, #d946ef 50%, #8b5cf6 100%);
  color: #ffffff;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 6px 20px rgba(234, 88, 12, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-honest-confirm:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 28px rgba(234, 88, 12, 0.45);
}

.btn-honest-confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-not-yet {
  width: 100%;
  padding: 11px 20px;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.04);
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-not-yet:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.08);
  color: #ffffff;
}

/* ── Spinner ─────────────────────────────── */
.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

/* ── Keyframes ───────────────────────────── */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: scale(0.94) translateY(14px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

@keyframes pulseRing {
  0% { transform: scale(0.95); opacity: 0.8; }
  50% { transform: scale(1.15); opacity: 0; }
  100% { transform: scale(0.95); opacity: 0; }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 480px) {
  .modal-card {
    padding: 26px 18px 22px;
    border-radius: 20px;
  }
  .modal-title {
    font-size: 1.18rem;
  }
}
</style>
