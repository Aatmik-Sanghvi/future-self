<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import FooterSection from '@/components/FooterSection.vue'
import DailyMissionCard from '@/components/DailyMissionCard.vue'
import MissionCompleteModal from '@/components/MissionCompleteModal.vue'
import MoodPopup from '@/components/MoodPopup.vue'
import missionService from '@/services/missionService'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()

// State
const loadingToday = ref(true)
const regenerating = ref(false)
const completing = ref(false)
const todayMission = ref(null)
const todayMood = ref('')
const isMoodCheckedIn = ref(false)
const showMoodModal = ref(false)

// History state
const loadingHistory = ref(false)
const historyMissions = ref([])
const historyPagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
})
const selectedFilter = ref('all') // 'all', 'completed', 'pending'
const stats = ref({
  total_missions: 0,
  completed_missions: 0,
  pending_missions: 0,
  completion_rate: 0,
  total_minutes_invested: 0,
  daily_streak: 0,
})

// Modal state
const showCompleteModal = ref(false)
const missionToComplete = ref(null)
const showConfetti = ref(false)

// Fetch Today's Mission
async function fetchTodayMission() {
  loadingToday.value = true
  try {
    const res = await missionService.getTodayMission()
    const data = res.data.data
    todayMission.value = data.mission
    todayMood.value = data.today_mood || ''
    isMoodCheckedIn.value = data.is_mood_checked_in
    if (data.stats) {
      stats.value = data.stats
    }
  } catch (err) {
    console.error('Failed to fetch today mission:', err)
  } finally {
    loadingToday.value = false
  }
}

// Fetch History
async function fetchHistory(page = 1) {
  loadingHistory.value = true
  try {
    const params = {
      page,
      status: selectedFilter.value === 'all' ? null : selectedFilter.value,
    }
    const res = await missionService.getHistory(params)
    const data = res.data.data
    historyMissions.value = data.missions.data || []
    historyPagination.value = {
      current_page: data.missions.current_page,
      last_page: data.missions.last_page,
      total: data.missions.total,
    }
    if (data.stats) {
      stats.value = data.stats
    }
  } catch (err) {
    console.error('Failed to fetch history:', err)
  } finally {
    loadingHistory.value = false
  }
}

// Regenerate today's mission
async function handleRegenerate() {
  regenerating.value = true
  try {
    const res = await missionService.generateMission()
    todayMission.value = res.data.data.mission
    if (res.data.data.stats) {
      stats.value = res.data.data.stats
    }
    auth.toastMessage('Fresh mission created by your Future Self!', 'success')
    await fetchHistory(historyPagination.value.current_page)
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to generate mission'
    auth.toastMessage(msg, 'error')
  } finally {
    regenerating.value = false
  }
}

// Open Completion Modal
function openCompleteModal(mission) {
  missionToComplete.value = mission || todayMission.value
  showCompleteModal.value = true
}

// Confirm Completion (Honesty Check passed)
async function confirmComplete(payload) {
  if (!missionToComplete.value) return
  completing.value = true
  try {
    const res = await missionService.completeMission(missionToComplete.value.id, payload)
    showCompleteModal.value = false
    
    // Trigger celebration
    triggerConfettiAnimation()
    
    if (res.data.data.mission) {
      if (todayMission.value && todayMission.value.id === res.data.data.mission.id) {
        todayMission.value = res.data.data.mission
      }
    }
    if (res.data.data.stats) {
      stats.value = res.data.data.stats
    }
    if (res.data.data.daily_streak !== undefined) {
      auth.user.daily_streak = res.data.data.daily_streak
    }

    auth.toastMessage('🎉 Incredible work! Daily mission completed honestly.', 'success')
    await fetchHistory(historyPagination.value.current_page)
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to complete mission'
    auth.toastMessage(msg, 'error')
  } finally {
    completing.value = false
  }
}

function triggerConfettiAnimation() {
  showConfetti.value = true
  setTimeout(() => {
    showConfetti.value = false
  }, 4000)
}

function setFilter(filter) {
  selectedFilter.value = filter
  fetchHistory(1)
}

function goToPage(page) {
  if (page < 1 || page > historyPagination.value.last_page || page === historyPagination.value.current_page) return
  fetchHistory(page)
  const el = document.getElementById('mission-history-section')
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

const visiblePages = computed(() => {
  const current = historyPagination.value.current_page
  const total = historyPagination.value.last_page
  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1)
  }
  const pages = []
  if (current <= 3) {
    pages.push(1, 2, 3, 4, '...', total)
  } else if (current >= total - 2) {
    pages.push(1, '...', total - 3, total - 2, total - 1, total)
  } else {
    pages.push(1, '...', current - 1, current, current + 1, '...', total)
  }
  return pages
})

const historyItemRange = computed(() => {
  const total = historyPagination.value.total
  if (total === 0) return '0 missions'
  const start = (historyPagination.value.current_page - 1) * 10 + 1
  const end = Math.min(historyPagination.value.current_page * 10, total)
  return `Showing ${start}–${end} of ${total} missions`
})

function onMoodSubmitted() {
  showMoodModal.value = false
  fetchTodayMission()
  auth.toastMessage('Mood recorded! Your missions are aligned with your energy.', 'success')
}

// Reminder Settings State
const showReminderModal = ref(false)
const savingReminder = ref(false)
const reminderSettings = ref({
  mission_email_enabled: true,
  mission_reminder_time: '19:00',
  mission_reminder_enabled: true,
  default_reminder_time: '19:00',
})

// Only allowed times at 4:00 PM or after with 30-min gap (4:00 PM to 11:30 PM)
const reminderTimeOptions = [
  { label: '4:00 PM', value: '16:00' },
  { label: '4:30 PM', value: '16:30' },
  { label: '5:00 PM', value: '17:00' },
  { label: '5:30 PM', value: '17:30' },
  { label: '6:00 PM', value: '18:00' },
  { label: '6:30 PM', value: '18:30' },
  { label: '7:00 PM (Default)', value: '19:00' },
  { label: '7:30 PM', value: '19:30' },
  { label: '8:00 PM', value: '20:00' },
  { label: '8:30 PM', value: '20:30' },
  { label: '9:00 PM', value: '21:00' },
  { label: '9:30 PM', value: '21:30' },
  { label: '10:00 PM', value: '22:00' },
  { label: '10:30 PM', value: '22:30' },
  { label: '11:00 PM', value: '23:00' },
  { label: '11:30 PM', value: '23:30' },
]

async function fetchReminderSettings() {
  try {
    const res = await missionService.getReminderSettings()
    if (res.data?.data) {
      reminderSettings.value = {
        ...reminderSettings.value,
        ...res.data.data,
      }
    }
  } catch (err) {
    console.error('Failed to fetch reminder settings:', err)
  }
}

async function saveReminderSettings() {
  // Validate selected time is at 4:00 PM or later in 30-min intervals
  if (reminderSettings.value.mission_reminder_enabled && reminderSettings.value.mission_reminder_time) {
    const timeVal = reminderSettings.value.mission_reminder_time
    const isValid = /^(1[6-9]|2[0-3]):(00|30)$/.test(timeVal)
    if (!isValid) {
      auth.toastMessage('Reminder time must be at 4:00 PM or later in 30-minute intervals (e.g. 4:00 PM, 4:30 PM).', 'error')
      return
    }
  }

  savingReminder.value = true
  try {
    const res = await missionService.updateReminderSettings({
      mission_email_enabled: reminderSettings.value.mission_email_enabled,
      mission_reminder_time: reminderSettings.value.mission_reminder_time,
      mission_reminder_enabled: reminderSettings.value.mission_reminder_enabled,
    })
    auth.toastMessage(res.data?.message || 'Mission reminder preferences saved!', 'success')
    showReminderModal.value = false
  } catch (err) {
    auth.toastMessage(err.response?.data?.message || 'Failed to save reminder preferences', 'error')
  } finally {
    savingReminder.value = false
  }
}

function selectReminderTime(timeValue) {
  reminderSettings.value.mission_reminder_time = timeValue
}

function formatTimeDisplay(timeStr) {
  if (!timeStr) return '7:00 PM'
  const parts = timeStr.split(':')
  if (parts.length < 2) return timeStr
  let hour = parseInt(parts[0], 10)
  const minute = parts[1]
  const ampm = hour >= 12 ? 'PM' : 'AM'
  hour = hour % 12 || 12
  return `${hour}:${minute} ${ampm}`
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString(undefined, {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

function checkOpenSettings() {
  if (
    route.query.settings === '1' ||
    route.query.settings === 'true' ||
    route.query.reminders === '1' ||
    route.query.reminders === 'true' ||
    route.query.email_settings === '1' ||
    route.query.email_settings === 'true'
  ) {
    showReminderModal.value = true
  }
}

watch(() => route.query, () => {
  checkOpenSettings()
})

onMounted(() => {
  fetchTodayMission()
  fetchHistory(1)
  fetchReminderSettings()
  checkOpenSettings()
})
</script>

<template>
  <div class="missions-page-root">
    <Navbar />

    <!-- Confetti Particle Celebration Overlay -->
    <div v-if="showConfetti" class="confetti-container" aria-hidden="true">
      <div v-for="i in 40" :key="i" class="confetti-piece" :style="{
        '--x': Math.random() * 100 + 'vw',
        '--delay': Math.random() * 0.8 + 's',
        '--color': ['#a855f7', '#ec4899', '#f97316', '#3b82f6', '#22c55e', '#eab308'][i % 6]
      }"></div>
    </div>

    <!-- Modals -->
    <MissionCompleteModal
      v-if="showCompleteModal && missionToComplete"
      :mission="missionToComplete"
      :loading="completing"
      @confirm="confirmComplete"
      @close="showCompleteModal = false"
    />

    <MoodPopup
      v-if="showMoodModal"
      @close="onMoodSubmitted"
    />

    <!-- Reminder Settings Modal -->
    <Transition name="modal-fade">
      <div v-if="showReminderModal" class="reminder-modal-overlay" @click.self="showReminderModal = false">
        <div class="reminder-modal-card">
          <div class="modal-header-row">
            <div class="modal-title-group">
              <div class="modal-icon-badge">⏰</div>
              <div>
                <h3 class="reminder-modal-title">Mission Email & Reminder Settings</h3>
                <p class="reminder-modal-subtitle">Configure morning deliveries and evening streak check-ins.</p>
              </div>
            </div>
            <button class="btn-modal-close" @click="showReminderModal = false" type="button" aria-label="Close modal">✕</button>
          </div>

          <div class="reminder-modal-body">
            <!-- 8:00 AM Morning Email Toggle -->
            <div class="reminder-option-row">
              <div class="option-text-col">
                <div class="option-label-title">☀️ Morning Mission Delivery</div>
                <div class="option-label-desc">Receive today's action plan from your future self daily at <strong>8:00 AM</strong>.</div>
              </div>
              <label class="switch-control">
                <input type="checkbox" v-model="reminderSettings.mission_email_enabled" />
                <span class="slider round"></span>
              </label>
            </div>

            <!-- Evening Pending Reminder Toggle -->
            <div class="reminder-option-row">
              <div class="option-text-col">
                <div class="option-label-title">⏳ Evening Pending Reminder</div>
                <div class="option-label-desc">Receive a reminder before midnight if your daily mission is still pending.</div>
              </div>
              <label class="switch-control">
                <input type="checkbox" v-model="reminderSettings.mission_reminder_enabled" />
                <span class="slider round"></span>
              </label>
            </div>

            <!-- Reminder Time Picker (when enabled) -->
            <div class="reminder-time-section" v-if="reminderSettings.mission_reminder_enabled">
              <div class="time-section-header">
                <label class="time-section-title">Preferred Evening Reminder Time:</label>
                <span class="time-section-badge">4:00 PM onwards (30-min intervals)</span>
              </div>
              <div class="time-pills-grid">
                <button
                  v-for="opt in reminderTimeOptions"
                  :key="opt.value"
                  type="button"
                  class="time-pill-btn"
                  :class="{ active: reminderSettings.mission_reminder_time === opt.value }"
                  @click="selectReminderTime(opt.value)"
                >
                  {{ opt.label }}
                </button>
              </div>

              <div class="default-time-hint">
                💡 <strong>Schedule:</strong> Reminders can only be set at <strong>4:00 PM or later</strong> in <strong>30-minute intervals</strong> (Default: 7:00 PM). Reminders are automatically skipped if you've already completed today's mission.
              </div>
            </div>
          </div>

          <div class="reminder-modal-footer">
            <button class="btn-cancel" @click="showReminderModal = false" type="button">Cancel</button>
            <button class="btn-save-reminder" @click="saveReminderSettings" :disabled="savingReminder" type="button">
              <span v-if="savingReminder" class="spinner-small"></span>
              <span>{{ savingReminder ? 'Saving...' : 'Save Preferences' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Main Container -->
    <main class="missions-main-container">
      <!-- Page Hero Header -->
      <section class="missions-hero-header">
        <div class="header-badge">
          <span class="sparkle-icon">✨</span>
          <span>Future Self Action Blueprint</span>
        </div>
        <h1 class="missions-hero-title">Daily Missions & Growth Log</h1>
        <p class="missions-hero-subtitle">
          One tailored daily action assigned by your future self, calibrated to your current mood and lifelong goals.
        </p>
      </section>

      <!-- Stats Overview Bar -->
      <section class="stats-cards-grid">
        <!-- Streak Card -->
        <div class="stat-card streak-stat-card">
          <div class="stat-icon-wrapper">
            <span class="stat-flame-icon">🔥</span>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ auth.dailyStreak || stats.daily_streak || 0 }}</div>
            <div class="stat-label">Day Streak</div>
          </div>
        </div>

        <!-- Completed Card -->
        <div class="stat-card">
          <div class="stat-icon-wrapper purple-wrapper">
            <span>🎯</span>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ stats.completed_missions }}</div>
            <div class="stat-label">Missions Done</div>
          </div>
        </div>

        <!-- Success Rate Card -->
        <div class="stat-card">
          <div class="stat-icon-wrapper blue-wrapper">
            <span>📈</span>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ stats.completion_rate }}%</div>
            <div class="stat-label">Completion Rate</div>
          </div>
        </div>

        <!-- Minutes Invested Card -->
        <div class="stat-card">
          <div class="stat-icon-wrapper green-wrapper">
            <span>⏱️</span>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ stats.total_minutes_invested }}m</div>
            <div class="stat-label">Focus Invested</div>
          </div>
        </div>
      </section>

      <!-- Mission Schedule & Reminder Bar -->
      <section class="mission-schedule-banner">
        <div class="schedule-banner-left">
          <div class="schedule-icon-wrapper">
            <span>⏰</span>
          </div>
          <div class="schedule-banner-content">
            <div class="schedule-header-line">
              <span class="schedule-main-label">Daily Email & Evening Reminders</span>
              <span class="status-pill" :class="reminderSettings.mission_email_enabled ? 'status-active' : 'status-inactive'">
                {{ reminderSettings.mission_email_enabled ? '☀️ 8:00 AM Active' : 'Morning Off' }}
              </span>
              <span class="status-pill" :class="reminderSettings.mission_reminder_enabled ? 'status-active' : 'status-inactive'">
                {{ reminderSettings.mission_reminder_enabled ? `⏳ Reminder: ${formatTimeDisplay(reminderSettings.mission_reminder_time)}` : 'Reminders Off' }}
              </span>
            </div>
            <p class="schedule-subtext">
              Missions are sent at 8:00 AM. If uncompleted, a reminder is sent at {{ formatTimeDisplay(reminderSettings.mission_reminder_time) }} (Default: 7:00 PM).
            </p>
          </div>
        </div>
        <button class="btn-config-reminder" @click="showReminderModal = true" type="button">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
          </svg>
          <span>Reminder Settings</span>
        </button>
      </section>

      <!-- Active Today's Mission Section -->
      <section class="today-mission-section">
        <div class="section-title-row">
          <h2 class="section-title">Today's Focus</h2>
          <button
            v-if="!isMoodCheckedIn"
            type="button"
            class="btn-mood-checkin-link"
            @click="showMoodModal = true"
          >
            <span>Check in your mood</span>
            <span class="arrow-right">→</span>
          </button>
        </div>

        <DailyMissionCard
          :mission="todayMission"
          :loading="loadingToday"
          :regenerating="regenerating"
          :today-mood="todayMood"
          @complete="openCompleteModal(todayMission)"
          @regenerate="handleRegenerate"
        />
      </section>

      <!-- Mission History & Journey Log -->
      <section class="mission-history-section" id="mission-history-section">
        <div class="history-header">
          <div>
            <h2 class="section-title">Mission Journey & History</h2>
            <p class="history-subtitle">
              Review your consistency, past achievements, and reflections.
              <span class="history-range-tag" v-if="historyPagination.total > 0">{{ historyItemRange }}</span>
            </p>
          </div>

          <!-- Filter Pills -->
          <div class="filter-pills-row">
            <button
              class="filter-pill"
              :class="{ active: selectedFilter === 'all' }"
              @click="setFilter('all')"
            >
              All ({{ stats.total_missions }})
            </button>
            <button
              class="filter-pill"
              :class="{ active: selectedFilter === 'completed' }"
              @click="setFilter('completed')"
            >
              Completed ({{ stats.completed_missions }})
            </button>
            <button
              class="filter-pill"
              :class="{ active: selectedFilter === 'pending' }"
              @click="setFilter('pending')"
            >
              Pending ({{ stats.pending_missions }})
            </button>
          </div>
        </div>

        <!-- History Items List -->
        <div class="history-list" v-if="!loadingHistory && historyMissions.length > 0">
          <div
            v-for="item in historyMissions"
            :key="item.id"
            class="history-card"
            :class="{ 'history-card-completed': item.status === 'completed' }"
          >
            <div class="history-card-left">
              <div class="history-date-row">
                <span class="history-date">{{ formatDate(item.mission_date) }}</span>
                <span
                  class="history-status-badge"
                  :class="item.status === 'completed' ? 'badge-done' : 'badge-pending'"
                >
                  {{ item.status === 'completed' ? '✓ Completed' : '○ Pending' }}
                </span>
                <span class="history-category" v-if="item.category">{{ item.category }}</span>
              </div>

              <h4 class="history-item-title">{{ item.title }}</h4>
              <p class="history-item-desc">{{ item.description }}</p>

              <!-- Future Self Note -->
              <div class="history-note" v-if="item.future_self_note">
                <span class="history-note-avatar">✨</span>
                <span>"{{ item.future_self_note }}"</span>
              </div>

              <!-- Reflection if completed -->
              <div class="history-reflection" v-if="item.reflection">
                <span class="reflection-tag">Your Reflection:</span>
                <p class="reflection-quote">"{{ item.reflection }}"</p>
              </div>
            </div>

            <div class="history-card-right">
              <div class="history-meta">
                <span class="meta-time" v-if="item.estimated_minutes">⏱️ {{ item.estimated_minutes }}m</span>
                <span class="meta-diff" v-if="item.difficulty">{{ item.difficulty.toUpperCase() }}</span>
              </div>

              <button
                v-if="item.status !== 'completed'"
                class="btn-history-complete"
                @click="openCompleteModal(item)"
              >
                Mark Complete
              </button>
            </div>
          </div>

          <!-- Pagination Bar -->
          <div class="pagination-container" v-if="historyPagination.last_page > 1">
            <button
              class="pagination-btn pagination-prev-next"
              :disabled="historyPagination.current_page <= 1"
              @click="goToPage(historyPagination.current_page - 1)"
              aria-label="Previous page"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
              </svg>
              <span>Prev</span>
            </button>

            <div class="pagination-pages-group">
              <template v-for="(p, idx) in visiblePages" :key="idx">
                <span v-if="p === '...'" class="pagination-ellipsis">…</span>
                <button
                  v-else
                  class="pagination-page-btn"
                  :class="{ active: p === historyPagination.current_page }"
                  @click="goToPage(p)"
                >
                  {{ p }}
                </button>
              </template>
            </div>

            <button
              class="pagination-btn pagination-prev-next"
              :disabled="historyPagination.current_page >= historyPagination.last_page"
              @click="goToPage(historyPagination.current_page + 1)"
              aria-label="Next page"
            >
              <span>Next</span>
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Loading Skeleton -->
        <div class="history-loading" v-else-if="loadingHistory">
          <div class="loading-spinner"></div>
          <span>Loading mission journey...</span>
        </div>

        <!-- Empty History State -->
        <div class="history-empty" v-else>
          <div class="empty-emoji">🌱</div>
          <h3>No missions recorded yet</h3>
          <p>Your journey begins with today's mission. Complete it to build your consistency record!</p>
        </div>
      </section>
    </main>

    <FooterSection />
  </div>
</template>

<style scoped>
/* ── Page Layout ─────────────────────────── */
.missions-page-root {
  min-height: 100vh;
  background-color: #0b0b14;
  color: #f8fafc;
  display: flex;
  flex-direction: column;
  overflow-x: hidden;
  width: 100%;
  max-width: 100vw;
  box-sizing: border-box;
}

.missions-main-container {
  max-width: 1000px;
  width: 100%;
  margin: 0 auto;
  padding: 100px 20px 60px;
  flex: 1;
  box-sizing: border-box;
  overflow-x: hidden;
}

/* ── Hero Header ─────────────────────────── */
.missions-hero-header {
  text-align: center;
  margin-bottom: 36px;
}

.header-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 14px;
  border-radius: 999px;
  background: rgba(168, 85, 247, 0.12);
  border: 1px solid rgba(168, 85, 247, 0.3);
  font-size: 0.8rem;
  font-weight: 700;
  color: #d8b4fe;
  margin-bottom: 12px;
}

.missions-hero-title {
  font-size: 2.3rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  line-height: 1.2;
  margin: 0 0 12px;
  background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 50%, #c084fc 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.missions-hero-subtitle {
  font-size: 1.05rem;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.65);
  max-width: 600px;
  margin: 0 auto;
}

/* ── Stats Grid ──────────────────────────── */
.stats-cards-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 40px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 18px;
  border-radius: 18px;
  background: linear-gradient(145deg, rgba(255, 255, 255, 0.04) 0%, rgba(255, 255, 255, 0.02) 100%);
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
  transition: all 0.25s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  border-color: rgba(255, 255, 255, 0.15);
}

.streak-stat-card {
  background: linear-gradient(145deg, rgba(249, 115, 22, 0.1) 0%, rgba(255, 255, 255, 0.02) 100%);
  border-color: rgba(249, 115, 22, 0.25);
}

.stat-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: rgba(249, 115, 22, 0.15);
  font-size: 1.3rem;
  flex-shrink: 0;
}

.purple-wrapper { background: rgba(168, 85, 247, 0.15); }
.blue-wrapper { background: rgba(59, 130, 246, 0.15); }
.green-wrapper { background: rgba(34, 197, 94, 0.15); }

.stat-number {
  font-size: 1.45rem;
  font-weight: 800;
  color: #ffffff;
  line-height: 1.1;
}

.stat-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.5);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-top: 2px;
}

/* ── Section Titles ──────────────────────── */
.section-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.section-title {
  font-size: 1.35rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
  letter-spacing: -0.01em;
}

.btn-mood-checkin-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #c084fc;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-mood-checkin-link:hover {
  background: rgba(168, 85, 247, 0.15);
  color: #e9d5ff;
}

/* ── Mission Schedule & Reminder Banner ──── */
.mission-schedule-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 16px 20px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.08) 0%, rgba(249, 115, 22, 0.06) 100%);
  border: 1px solid rgba(168, 85, 247, 0.2);
  margin-bottom: 32px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

.schedule-banner-left {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}

.schedule-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: rgba(168, 85, 247, 0.15);
  font-size: 1.2rem;
  flex-shrink: 0;
}

.schedule-banner-content {
  min-width: 0;
}

.schedule-header-line {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 4px;
}

.schedule-main-label {
  font-size: 0.95rem;
  font-weight: 700;
  color: #ffffff;
}

.status-pill {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 999px;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.status-active {
  background: rgba(34, 197, 94, 0.15);
  border: 1px solid rgba(34, 197, 94, 0.35);
  color: #86efac;
}

.status-inactive {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.4);
}

.schedule-subtext {
  font-size: 0.82rem;
  color: rgba(255, 255, 255, 0.6);
  margin: 0;
  line-height: 1.4;
}

.btn-config-reminder {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 16px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #f1f5f9;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  flex-shrink: 0;
  transition: all 0.2s ease;
}

.btn-config-reminder:hover {
  background: rgba(168, 85, 247, 0.2);
  border-color: rgba(168, 85, 247, 0.4);
  color: #ffffff;
  transform: translateY(-1px);
}

/* ── Reminder Modal ──────────────────────── */
.reminder-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  background: rgba(5, 5, 12, 0.85);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  overflow-y: auto;
}

.reminder-modal-card {
  width: 100%;
  max-width: 520px;
  max-height: calc(100vh - 32px);
  border-radius: 20px;
  background: linear-gradient(150deg, #181827 0%, #11111d 100%);
  border: 1px solid rgba(168, 85, 247, 0.25);
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.6), 0 0 40px rgba(168, 85, 247, 0.1);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  margin: auto;
}

.modal-header-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 22px 24px 18px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  flex-shrink: 0;
}

.modal-title-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.modal-icon-badge {
  font-size: 1.5rem;
  padding: 8px;
  border-radius: 12px;
  background: rgba(168, 85, 247, 0.12);
  border: 1px solid rgba(168, 85, 247, 0.25);
}

.reminder-modal-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 2px;
}

.reminder-modal-subtitle {
  font-size: 0.82rem;
  color: rgba(255, 255, 255, 0.55);
  margin: 0;
}

.btn-modal-close {
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.4);
  font-size: 1.1rem;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: all 0.15s ease;
}

.btn-modal-close:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.08);
}

.reminder-modal-body {
  padding: 22px 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
  overflow-y: auto;
  max-height: 100%;
  overscroll-behavior: contain;
  scrollbar-width: thin;
  scrollbar-color: rgba(168, 85, 247, 0.35) transparent;
}

.reminder-modal-body::-webkit-scrollbar {
  width: 6px;
}

.reminder-modal-body::-webkit-scrollbar-track {
  background: transparent;
}

.reminder-modal-body::-webkit-scrollbar-thumb {
  background: rgba(168, 85, 247, 0.35);
  border-radius: 999px;
}

.reminder-option-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 14px 16px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
}

.option-text-col {
  min-width: 0;
}

.option-label-title {
  font-size: 0.92rem;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 2px;
}

.option-label-desc {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
  line-height: 1.4;
}

/* ── Toggle Switch ── */
.switch-control {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  flex-shrink: 0;
}

.switch-control input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  inset: 0;
  background-color: rgba(255, 255, 255, 0.15);
  transition: 0.3s;
  border-radius: 24px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
}

input:checked + .slider {
  background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%);
}

input:checked + .slider:before {
  transform: translateX(20px);
}

/* ── Reminder Time Section ── */
.reminder-time-section {
  padding: 16px;
  border-radius: 14px;
  background: rgba(168, 85, 247, 0.06);
  border: 1px solid rgba(168, 85, 247, 0.2);
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.time-section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.time-section-badge {
  font-size: 0.72rem;
  font-weight: 600;
  color: #c084fc;
  background: rgba(192, 132, 252, 0.12);
  border: 1px solid rgba(192, 132, 252, 0.25);
  padding: 2px 8px;
  border-radius: 20px;
}

.time-section-title {
  font-size: 0.85rem;
  font-weight: 700;
  color: #e9d5ff;
}

.time-pills-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
  max-height: 180px;
  overflow-y: auto;
  padding-right: 2px;
}

.time-pill-btn {
  padding: 8px 6px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.75);
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: center;
}

.time-pill-btn:hover {
  background: rgba(168, 85, 247, 0.15);
  color: #ffffff;
}

.time-pill-btn.active {
  background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%);
  border-color: #c084fc;
  color: #ffffff;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(168, 85, 247, 0.35);
}

.default-time-hint {
  font-size: 0.78rem;
  color: rgba(255, 255, 255, 0.55);
  line-height: 1.45;
  padding: 8px 12px;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.2);
}

.reminder-modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 24px 22px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  flex-shrink: 0;
}

.btn-cancel {
  padding: 9px 18px;
  border-radius: 10px;
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-cancel:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #ffffff;
}

.btn-save-reminder {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 22px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%);
  color: #ffffff;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 16px rgba(168, 85, 247, 0.35);
}

.btn-save-reminder:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 22px rgba(168, 85, 247, 0.5);
}

.today-mission-section {
  margin-bottom: 48px;
}

/* ── History Section ─────────────────────── */
.history-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 20px;
}

.history-subtitle {
  font-size: 0.88rem;
  color: rgba(255, 255, 255, 0.55);
  margin: 4px 0 0;
}

.filter-pills-row {
  display: flex;
  gap: 8px;
}

.filter-pill {
  padding: 6px 14px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-pill:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.08);
}

.filter-pill.active {
  background: rgba(168, 85, 247, 0.2);
  border-color: rgba(168, 85, 247, 0.45);
  color: #f1f5f9;
}

/* ── History Cards ───────────────────────── */
.history-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.history-card {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 18px;
  padding: 20px;
  border-radius: 18px;
  background: linear-gradient(150deg, rgba(255, 255, 255, 0.03) 0%, rgba(255, 255, 255, 0.01) 100%);
  border: 1px solid rgba(255, 255, 255, 0.06);
  transition: all 0.2s ease;
}

.history-card:hover {
  border-color: rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.04);
}

.history-card-completed {
  border-left: 3px solid #22c55e;
}

.history-card-left {
  flex: 1;
}

.history-date-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
  flex-wrap: wrap;
}

.history-date {
  font-size: 0.78rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.5);
}

.history-status-badge {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
}

.badge-done {
  background: rgba(34, 197, 94, 0.15);
  color: #4ade80;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.badge-pending {
  background: rgba(234, 179, 8, 0.15);
  color: #facc15;
  border: 1px solid rgba(234, 179, 8, 0.3);
}

.history-category {
  font-size: 0.72rem;
  padding: 2px 8px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.06);
  color: rgba(255, 255, 255, 0.7);
}

.history-item-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 6px;
}

.history-item-desc {
  font-size: 0.88rem;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.7);
  margin: 0 0 10px;
}

.history-note {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.82rem;
  font-style: italic;
  color: #c084fc;
  margin-bottom: 8px;
}

.history-reflection {
  padding: 8px 12px;
  border-radius: 8px;
  background: rgba(34, 197, 94, 0.06);
  border: 1px solid rgba(34, 197, 94, 0.15);
  margin-top: 6px;
}

.reflection-tag {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #4ade80;
  display: block;
}

.reflection-quote {
  font-size: 0.82rem;
  color: rgba(255, 255, 255, 0.85);
  margin: 2px 0 0;
}

.history-card-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 12px;
  flex-shrink: 0;
}

.history-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
}

.btn-history-complete {
  padding: 7px 14px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #ffffff;
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-history-complete:hover {
  transform: scale(1.04);
}

.history-range-tag {
  display: inline-block;
  margin-left: 8px;
  padding: 2px 8px;
  border-radius: 6px;
  background: rgba(168, 85, 247, 0.12);
  border: 1px solid rgba(168, 85, 247, 0.25);
  font-size: 0.75rem;
  font-weight: 600;
  color: #d8b4fe;
}

/* ── Pagination ──────────────────────────── */
.pagination-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-top: 28px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  flex-wrap: wrap;
}

.pagination-pages-group {
  display: flex;
  align-items: center;
  gap: 6px;
}

.pagination-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 7px 14px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.04);
  color: rgba(255, 255, 255, 0.85);
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.pagination-btn:hover:not(:disabled) {
  background: rgba(168, 85, 247, 0.18);
  border-color: rgba(168, 85, 247, 0.4);
  color: #ffffff;
  transform: translateY(-1px);
}

.pagination-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.pagination-page-btn {
  min-width: 34px;
  height: 34px;
  padding: 0 8px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.pagination-page-btn:hover:not(.active) {
  background: rgba(255, 255, 255, 0.08);
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.16);
  transform: translateY(-1px);
}

.pagination-page-btn.active {
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.3) 0%, rgba(99, 102, 241, 0.3) 100%);
  border-color: rgba(168, 85, 247, 0.6);
  color: #ffffff;
  box-shadow: 0 0 14px rgba(168, 85, 247, 0.35);
}

.pagination-ellipsis {
  padding: 0 4px;
  color: rgba(255, 255, 255, 0.4);
  font-size: 0.85rem;
}

/* ── Empty & Loading ─────────────────────── */
.history-empty, .history-loading {
  text-align: center;
  padding: 40px 20px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px dashed rgba(255, 255, 255, 0.08);
}

.empty-emoji {
  font-size: 2rem;
  margin-bottom: 8px;
}

.history-empty h3 {
  font-size: 1.1rem;
  color: #ffffff;
  margin: 0 0 4px;
}

.history-empty p {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.5);
  margin: 0;
}

.loading-spinner {
  width: 24px;
  height: 24px;
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-top-color: #a855f7;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  margin: 0 auto 10px;
}

/* ── Confetti Animation ──────────────────── */
.confetti-container {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 10000;
  overflow: hidden;
}

.confetti-piece {
  position: absolute;
  top: -20px;
  left: var(--x);
  width: 10px;
  height: 16px;
  background: var(--color);
  opacity: 0.9;
  border-radius: 3px;
  animation: confettiFall 3.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) var(--delay) forwards;
}

@keyframes confettiFall {
  0% {
    transform: translateY(0) rotate(0deg);
    opacity: 1;
  }
  100% {
    transform: translateY(110vh) rotate(720deg);
    opacity: 0;
  }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 1024px) {
  .missions-main-container {
    padding: 85px 20px 50px;
  }
  .stats-cards-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
  }
}

@media (max-width: 768px) {
  .missions-main-container {
    padding: 75px 16px 45px;
  }
  .stats-cards-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }
  .history-card {
    flex-direction: column;
  }
  .history-card-right {
    width: 100%;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    padding-top: 10px;
  }
  .missions-hero-title {
    font-size: 1.75rem;
  }
}

@media (max-width: 640px) {
  .missions-main-container {
    padding: 70px 12px 40px;
  }
  .missions-hero-title {
    font-size: 1.5rem;
  }
  .missions-hero-subtitle {
    font-size: 0.9rem;
  }
  .stats-cards-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    margin-bottom: 28px;
  }
  .stat-card {
    padding: 12px 10px;
    gap: 8px;
    border-radius: 14px;
  }
  .stat-icon-wrapper {
    width: 34px;
    height: 34px;
    font-size: 1rem;
    border-radius: 10px;
  }
  .stat-number {
    font-size: 1.15rem;
  }
  .stat-label {
    font-size: 0.68rem;
  }
  .history-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  .filter-pills-row {
    width: 100%;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: none;
  }
  .filter-pills-row::-webkit-scrollbar {
    display: none;
  }
  .pagination-container {
    gap: 6px;
  }
  .pagination-page-btn {
    min-width: 30px;
    height: 30px;
    font-size: 0.78rem;
    padding: 0 6px;
  }
  .pagination-btn {
    padding: 6px 10px;
    font-size: 0.78rem;
  }
  .mission-schedule-banner {
    flex-direction: column;
    align-items: stretch;
    padding: 14px 12px;
    gap: 12px;
  }
  .schedule-banner-left {
    align-items: flex-start;
  }
  .btn-config-reminder {
    width: 100%;
    justify-content: center;
  }
  .time-pills-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .modal-header-row {
    padding: 16px 18px;
  }
  .reminder-modal-body {
    padding: 16px 18px;
    gap: 14px;
  }
  .reminder-modal-footer {
    padding: 14px 18px 18px;
  }
}

@media (max-width: 480px) {
  .missions-main-container {
    padding: 60px 10px 30px;
  }
  .header-badge {
    font-size: 0.72rem;
    padding: 4px 10px;
    margin-bottom: 8px;
  }
  .missions-hero-title {
    font-size: 1.35rem;
    margin-bottom: 8px;
    word-break: break-word;
  }
  .missions-hero-subtitle {
    font-size: 0.84rem;
    line-height: 1.45;
  }
  .section-title {
    font-size: 1.15rem;
  }
  .btn-mood-checkin-link {
    font-size: 0.75rem;
    padding: 4px 8px;
  }
  .history-card {
    padding: 14px 12px;
    border-radius: 14px;
  }
  .history-item-title {
    font-size: 0.95rem;
  }
  .history-item-desc {
    font-size: 0.82rem;
  }
}

@media (max-width: 360px) {
  .stats-cards-grid {
    grid-template-columns: 1fr;
  }
  .missions-hero-title {
    font-size: 1.2rem;
  }
}
</style>
