<script setup>
import { useAuthStore } from '@/stores/auth';
import GeneralService from '@/services/GeneralService';

const auth = useAuthStore();

const moodType = [
  {type: 'exhausted', icon: '🫠'},
  {type: 'sad', icon: '😔'},
  {type: 'neutral', icon: '😐'},
  {type: 'happy', icon: '😊'},
  {type: 'great', icon: '😄'}
];

const emit = defineEmits(['close'])

const selectMood = async (moodType) => {
  try{
    const payload = {
      mood_type: moodType,
    }
    await GeneralService.postMood(payload);
    emit('close')
  } catch (err) {
    console.log(err);
    const message = err?.response?.data?.message || err?.message || 'Failed to record mood';
    auth.toastMessage(message, { type: 'error' });
  }
}
</script>

<template>
  <Teleport to="body">
    <div class="mood-overlay" id="mood-popup-overlay" @click.self="emit('close')">
      <div class="mood-popup">
        <!-- Close button -->
        <button class="mood-close-btn" id="mood-close-btn" aria-label="Close mood popup" @click="emit('close')">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>

        <!-- Decorative glow -->
        <div class="mood-glow"></div>

        <!-- Title -->
        <h2 class="mood-title">How are you feeling today?</h2>

        <!-- Emoji row -->
        <div class="mood-emoji-row">
          <button 
            v-for="mood in moodType" 
            :key="mood.type" 
            class="mood-emoji-btn" 
            :id="'mood-btn-' + mood.type" 
            :aria-label="mood.type"
            @click="selectMood(mood.type)">
            {{ mood.icon }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
/* ── Overlay ─────────────────────────────── */
.mood-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* ── Popup Card ──────────────────────────── */
.mood-popup {
  position: relative;
  width: 420px;
  max-width: 90vw;
  padding: 36px 28px 32px;
  border-radius: 24px;
  background: linear-gradient(145deg, #1e1e2e 0%, #2a2a3d 100%);
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow:
    0 24px 80px rgba(0, 0, 0, 0.5),
    0 0 0 1px rgba(255, 255, 255, 0.04) inset,
    0 1px 0 rgba(255, 255, 255, 0.06) inset;
  text-align: center;
  overflow: hidden;
  animation: popupSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

/* ── Decorative glow ─────────────────────── */
.mood-glow {
  position: absolute;
  top: -60px;
  left: 50%;
  transform: translateX(-50%);
  width: 200px;
  height: 200px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(139, 92, 246, 0.25) 0%, transparent 70%);
  pointer-events: none;
  z-index: 0;
}

/* ── Close Button ────────────────────────── */
.mood-close-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 30px;
  height: 30px;
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

.mood-close-btn svg {
  width: 15px;
  height: 15px;
}

.mood-close-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  color: rgba(255, 255, 255, 0.9);
  transform: scale(1.08);
}

/* ── Title ───────────────────────────────── */
.mood-title {
  position: relative;
  z-index: 1;
  font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0 0 28px 0;
  letter-spacing: -0.02em;
  line-height: 1.3;
  background: linear-gradient(135deg, #f0f0f5 0%, #c4b5fd 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* ── Emoji Row ───────────────────────────── */
.mood-emoji-row {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

/* ── Emoji Button ────────────────────────── */
.mood-emoji-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  font-size: 1.85rem;
  line-height: 1;
  border-radius: 50%;
  border: 2px solid transparent;
  background: rgba(255, 255, 255, 0.05);
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
  padding: 0;
}

.mood-emoji-btn:hover {
  background: rgba(139, 92, 246, 0.12);
  border-color: rgba(139, 92, 246, 0.3);
  transform: scale(1.18);
  box-shadow: 0 6px 20px rgba(139, 92, 246, 0.2);
}

.mood-emoji-btn.selected {
  background: rgba(139, 92, 246, 0.2);
  border-color: rgba(139, 92, 246, 0.5);
  transform: scale(1.22);
  box-shadow:
    0 0 20px rgba(139, 92, 246, 0.25),
    0 4px 16px rgba(139, 92, 246, 0.15);
}

/* ── Slide-in animation ──────────────────── */
@keyframes popupSlideIn {
  from {
    opacity: 0;
    transform: scale(0.92) translateY(12px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* ── Responsive ──────────────────────────── */
@media (max-width: 480px) {
  .mood-popup {
    padding: 28px 20px 24px;
    border-radius: 20px;
    max-width: 92vw;
  }

  .mood-title {
    font-size: 1.1rem;
    margin-bottom: 22px;
  }

  .mood-emoji-row {
    gap: 8px;
  }

  .mood-emoji-btn {
    width: 48px;
    height: 48px;
    font-size: 1.5rem;
  }
}

@media (max-width: 360px) {
  .mood-popup {
    padding: 24px 14px 20px;
  }

  .mood-emoji-row {
    gap: 6px;
  }

  .mood-emoji-btn {
    width: 42px;
    height: 42px;
    font-size: 1.3rem;
  }

  .mood-title {
    font-size: 1rem;
    margin-bottom: 18px;
  }
}
</style>
