<script setup>
import { computed } from 'vue'

const props = defineProps({
  mission: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  regenerating: {
    type: Boolean,
    default: false,
  },
  todayMood: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['complete', 'regenerate'])

const isCompleted = computed(() => props.mission?.status === 'completed')

const difficultyColor = computed(() => {
  switch (props.mission?.difficulty) {
    case 'easy':
      return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
    case 'hard':
      return 'bg-rose-500/10 text-rose-400 border-rose-500/30'
    default:
      return 'bg-amber-500/10 text-amber-400 border-amber-500/30'
  }
})

const moodEmoji = computed(() => {
  const mood = props.mission?.mood_type || props.todayMood
  const map = {
    exhausted: '🫠 Exhausted',
    sad: '😔 Down',
    neutral: '😐 Steady',
    happy: '😊 Energetic',
    great: '😄 Peak Flow',
  }
  return map[mood] || null
})
</script>

<template>
  <div class="mission-card-container" :class="{ 'completed-glow': isCompleted }">
    <!-- Ambient glowing backdrop -->
    <div class="card-glow" :class="{ 'card-glow-completed': isCompleted }"></div>

    <div class="mission-card">
      <!-- Top header bar -->
      <div class="mission-card-header">
        <div class="header-left">
          <span class="mission-badge-today">
            <span class="badge-pulse" v-if="!isCompleted"></span>
            {{ isCompleted ? '✨ Mission Completed' : '🎯 Today\'s Mission' }}
          </span>

          <span class="category-pill" v-if="mission?.category">
            {{ mission.category }}
          </span>

          <span class="difficulty-pill" :class="difficultyColor" v-if="mission?.difficulty">
            {{ mission.difficulty.toUpperCase() }}
          </span>
        </div>

        <div class="header-right">
          <span class="time-estimate" v-if="mission?.estimated_minutes">
            ⏱️ {{ mission.estimated_minutes }} mins
          </span>

          <span class="mood-pill" v-if="moodEmoji">
            {{ moodEmoji }}
          </span>
        </div>
      </div>

      <!-- Main Mission Content -->
      <div class="mission-content" v-if="mission">
        <div class="title-row">
          <h3 class="mission-title" :class="{ 'line-through opacity-75': isCompleted }">
            {{ mission.title }}
          </h3>

          <div v-if="isCompleted" class="completed-check-icon" title="Completed!">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" /><polyline points="22 4 12 14.01 9 11.01" />
            </svg>
          </div>
        </div>

        <p class="mission-desc">{{ mission.description }}</p>

        <!-- Future Self Note Callout -->
        <div class="future-self-note-box" v-if="mission.future_self_note">
          <div class="note-header">
            <div class="note-avatar">✨</div>
            <span class="note-author">Note from Future You:</span>
          </div>
          <p class="note-quote">"{{ mission.future_self_note }}"</p>
        </div>

        <!-- Reflection Callout if Completed -->
        <div class="user-reflection-box" v-if="isCompleted && mission.reflection">
          <div class="reflection-label">Your Reflection:</div>
          <p class="reflection-text">"{{ mission.reflection }}"</p>
        </div>

        <!-- Action Footer -->
        <div class="mission-actions">
          <div v-if="!isCompleted" class="action-buttons-row">
            <button
              type="button"
              class="btn-complete-mission"
              id="btn-open-complete-modal"
              :disabled="loading"
              @click="emit('complete')"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>
              <span>Mark as Completed</span>
            </button>

            <button
              type="button"
              class="btn-regen-mission"
              id="btn-regenerate-mission"
              :disabled="regenerating || loading"
              title="Generate a different mission for today"
              @click="emit('regenerate')"
            >
              <span v-if="regenerating" class="spinner-small"></span>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
              </svg>
              <span>{{ regenerating ? 'Generating...' : 'Refresh Task' }}</span>
            </button>
          </div>

          <div v-else class="completed-banner">
            <span class="completed-msg">🎉 Daily Mission Accomplished! Keep your momentum going.</span>
          </div>
        </div>
      </div>

      <!-- Empty State / Generating -->
      <div v-else class="mission-empty-state">
        <div class="empty-icon">⏳</div>
        <h4 class="empty-title">Crafting your personalized mission...</h4>
        <p class="empty-desc">Your Future Self is analyzing your goals and energy to prepare today's action step.</p>
        <button
          type="button"
          class="btn-primary-action"
          :disabled="regenerating"
          @click="emit('regenerate')"
        >
          <span v-if="regenerating" class="spinner-small"></span>
          <span v-else>Generate Today's Mission</span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ── Container & Backdrop ────────────────── */
.mission-card-container {
  position: relative;
  width: 100%;
  max-width: 100%;
  border-radius: 24px;
  box-sizing: border-box;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.card-glow {
  position: absolute;
  inset: 0;
  border-radius: 24px;
  background: radial-gradient(circle at 50% 0%, rgba(168, 85, 247, 0.2) 0%, rgba(249, 115, 22, 0.1) 60%, transparent 80%);
  filter: blur(10px);
  pointer-events: none;
  z-index: 0;
  transition: all 0.4s ease;
}

.card-glow-completed {
  background: radial-gradient(circle at 50% 0%, rgba(34, 197, 94, 0.25) 0%, rgba(59, 130, 246, 0.12) 60%, transparent 80%);
}

.mission-card {
  position: relative;
  z-index: 1;
  border-radius: 24px;
  background: linear-gradient(150deg, #181827 0%, #12121e 100%);
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow:
    0 16px 40px rgba(0, 0, 0, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.06);
  padding: 28px;
  overflow: hidden;
}

/* ── Header ──────────────────────────────── */
.mission-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.header-left, .header-right {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.mission-badge-today {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  border-radius: 999px;
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.18) 0%, rgba(249, 115, 22, 0.18) 100%);
  border: 1px solid rgba(168, 85, 247, 0.35);
  font-size: 0.82rem;
  font-weight: 700;
  color: #e9d5ff;
}

.badge-pulse {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #a855f7;
  animation: pulseDot 1.6s infinite ease-in-out;
}

.category-pill {
  padding: 4px 10px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  font-size: 0.75rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
}

.difficulty-pill {
  padding: 3px 8px;
  border-radius: 6px;
  border-width: 1px;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.04em;
}

.time-estimate, .mood-pill {
  font-size: 0.78rem;
  padding: 4px 10px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.7);
}

/* ── Content ─────────────────────────────── */
.title-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 10px;
}

.mission-title {
  font-size: 1.35rem;
  font-weight: 800;
  line-height: 1.3;
  letter-spacing: -0.02em;
  background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
}

.completed-check-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(34, 197, 94, 0.15);
  border: 1px solid rgba(34, 197, 94, 0.4);
  color: #4ade80;
  flex-shrink: 0;
}

.mission-desc {
  font-size: 0.95rem;
  line-height: 1.55;
  color: rgba(255, 255, 255, 0.78);
  margin: 0 0 20px;
}

/* ── Future Self Note Box ────────────────── */
.future-self-note-box {
  position: relative;
  padding: 16px 18px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.1) 0%, rgba(99, 102, 241, 0.08) 100%);
  border: 1px solid rgba(168, 85, 247, 0.25);
  margin-bottom: 22px;
}

.note-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
}

.note-avatar {
  font-size: 1rem;
  line-height: 1;
}

.note-author {
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #c084fc;
}

.note-quote {
  font-size: 0.9rem;
  font-style: italic;
  line-height: 1.45;
  color: #f1f5f9;
  margin: 0;
}

/* ── Reflection Box ──────────────────────── */
.user-reflection-box {
  padding: 12px 16px;
  border-radius: 12px;
  background: rgba(34, 197, 94, 0.06);
  border: 1px solid rgba(34, 197, 94, 0.2);
  margin-bottom: 20px;
}

.reflection-label {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #4ade80;
  margin-bottom: 4px;
}

.reflection-text {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.85);
  margin: 0;
}

/* ── Actions ─────────────────────────────── */
.mission-actions {
  margin-top: 10px;
}

.action-buttons-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.btn-complete-mission {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 22px;
  border-radius: 14px;
  border: none;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #ffffff;
  font-size: 0.92rem;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 18px rgba(16, 185, 129, 0.35);
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-complete-mission:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.5);
}

.btn-regen-mission {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 11px 16px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-regen-mission:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
}

.completed-banner {
  display: flex;
  align-items: center;
  padding: 12px 18px;
  border-radius: 12px;
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.completed-msg {
  font-size: 0.88rem;
  font-weight: 600;
  color: #4ade80;
}

/* ── Empty State ─────────────────────────── */
.mission-empty-state {
  text-align: center;
  padding: 24px 10px;
}

.empty-icon {
  font-size: 2.2rem;
  margin-bottom: 8px;
}

.empty-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 6px;
}

.empty-desc {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.6);
  max-width: 440px;
  margin: 0 auto 18px;
}

.btn-primary-action {
  padding: 11px 20px;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%);
  color: #ffffff;
  font-size: 0.88rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary-action:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(168, 85, 247, 0.4);
}

/* ── Spinner ─────────────────────────────── */
.spinner-small {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.25);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes pulseDot {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(0.7); }
}

@media (max-width: 640px) {
  .mission-card {
    padding: 20px 16px;
  }
  .mission-title {
    font-size: 1.15rem;
  }
  .action-buttons-row {
    flex-direction: column;
    align-items: stretch;
  }
  .btn-complete-mission, .btn-regen-mission {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .mission-card {
    padding: 16px 12px;
    border-radius: 18px;
  }
  .mission-title {
    font-size: 1.05rem;
    word-break: break-word;
  }
  .mission-desc {
    font-size: 0.84rem;
    line-height: 1.45;
  }
  .future-self-note-box {
    padding: 10px 12px;
  }
  .note-quote {
    font-size: 0.8rem;
  }
  .mission-badge-today, .category-pill, .difficulty-pill, .time-estimate, .mood-pill {
    font-size: 0.72rem;
    padding: 2px 6px;
  }
}
</style>
