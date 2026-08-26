<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Navbar from '@/components/Navbar.vue'
import FooterSection from '@/components/FooterSection.vue'
import goalService from '@/services/goalService'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

// State
const loading = ref(true)
const goalData = ref(null)
const momentum = ref(null)
const recentMissions = ref([])
const allGoals = ref([])
const selectedGoalId = ref(null)
const selectedTrendTimeframe = ref('7D') // '7D', '30D', 'Overall'
const showPastGoalsDrawer = ref(false)

// Modals
const showEditModal = ref(false)
const showProgressModal = ref(false)
const showAchieveConfirmModal = ref(false)
const isSubmitting = ref(false)

// Goal Form
const goalForm = ref({
  title: '',
  description: '',
  category: 'Growth',
  timeframe: 'long-term',
  priority: 3,
  target_value: null,
  current_value: 0,
  unit: '',
  status: 'active',
})

// Progress Form for Measurable Goals
const progressForm = ref({
  current_value: 0,
  increment: null,
  mode: 'increment', // 'set' or 'increment'
})

const categories = ['Career', 'Health', 'Skills', 'Mindset', 'Growth', 'Creativity', 'Finance', 'Relationships']

// Fetch Goal Tracking Data
async function fetchTrackingData(goalId = null) {
  loading.value = true
  try {
    const res = await goalService.getTracking(goalId)
    const data = res.data.data
    
    if (data.has_goal) {
      goalData.value = data.goal
      momentum.value = data.momentum
      recentMissions.value = data.recent_missions || []
      allGoals.value = data.all_goals || []
      selectedGoalId.value = data.goal.id
    } else {
      goalData.value = null
      momentum.value = null
      recentMissions.value = []
      allGoals.value = data.all_goals || []
      selectedGoalId.value = null
    }
  } catch (err) {
    console.error('Failed to load goal tracking data:', err)
    auth.toastMessage('Unable to load goal tracking metrics.', 'error')
  } finally {
    loading.value = false
  }
}

// Past Completed Goals (Archived Milestones)
const completedPastGoals = computed(() => {
  return allGoals.value.filter(g => g.status === 'completed' && g.id !== selectedGoalId.value)
})

const isGoalAchieved = computed(() => {
  return momentum.value?.status === 'achieved' || goalData.value?.status === 'completed'
})

// Status Display Configuration
const statusConfig = computed(() => {
  const status = momentum.value?.status || 'at_risk'
  const score = momentum.value?.score ?? 0

  if (isGoalAchieved.value) {
    return {
      label: 'Goal Achieved',
      badgeClass: 'badge-achieved',
      colorClass: 'color-emerald',
      icon: '🏆',
      haloClass: 'halo-emerald',
      headline: 'Milestone Accomplished!',
      description: 'An extraordinary testament to your consistency and courage. You brought your future self into the present.',
    }
  }

  switch (status) {
    case 'on_track':
      return {
        label: 'Strong Momentum',
        badgeClass: 'badge-strong',
        colorClass: 'color-emerald',
        icon: '⚡',
        haloClass: 'halo-emerald',
        headline: "You're moving with power and consistency",
        description: "Your daily actions are actively compounding. Keep this momentum flowing—your future self is built right here.",
      }
    case 'needs_attention':
      return {
        label: score >= 55 ? 'Building Momentum' : 'Needs Attention',
        badgeClass: 'badge-attention',
        colorClass: 'color-amber',
        icon: '🌱',
        haloClass: 'halo-amber',
        headline: 'Momentum is waiting to be ignited',
        description: 'You have solid foundations. Completing 1 or 2 small actions this week will push you into strong momentum.',
      }
    case 'at_risk':
    default:
      return {
        label: 'Momentum Slowing',
        badgeClass: 'badge-risk',
        colorClass: 'color-rose',
        icon: '🔥',
        haloClass: 'halo-rose',
        headline: 'Every journey has quiet days',
        description: "Action creates motivation, not the other way around. Complete one gentle micro-mission today to restart your fire.",
      }
  }
})

// Trend Display Configuration
const trendConfig = computed(() => {
  const trend = momentum.value?.recent_trend || 'stable'
  const details = momentum.value?.trend_details || {}
  const diff = details.diff || 0

  if (trend === 'improving') {
    return {
      title: 'Momentum is Rising',
      class: 'trend-improving',
      icon: '↗',
      text: `+${Math.abs(diff)} completed action${Math.abs(diff) === 1 ? '' : 's'} compared to the previous 7 days. Your pace is accelerating.`,
    }
  }
  if (trend === 'declining') {
    return {
      title: 'Pace has Softened',
      class: 'trend-declining',
      icon: '↘',
      text: `${Math.abs(diff)} fewer completed action${Math.abs(diff) === 1 ? '' : 's'} than the previous 7 days. A gentle 10-minute focus session today will turn this upward.`,
    }
  }
  return {
    title: 'Steady & Consistent',
    class: 'trend-stable',
    icon: '→',
    text: 'You are maintaining a reliable baseline. Consistency over time beats sporadic intensity.',
  }
})

// Future Self Insight
const futureSelfInsight = computed(() => {
  const score = momentum.value?.score ?? 0
  const streak = momentum.value?.current_streak ?? 0
  const title = goalData.value?.title || 'your goal'

  if (isGoalAchieved.value) {
    return `We did it. Looking back, every small micro-mission you stayed honest with led directly to this achievement. Take a moment to honor this milestone, then let us decide what mountain to climb next.`
  }
  if (score >= 80 && streak >= 3) {
    return `Looking back from the future, this was the exact period where everything shifted for us. Your consistency on "${title}" is creating unstoppable trajectory. Keep trusting the micro-actions.`
  }
  if (score >= 60) {
    return `You're laying down solid bricks each week toward "${title}". When resistance appears, remind yourself: we don't need a giant leap today, just one completed mission.`
  }
  if (score > 0) {
    return `I know some days feel demanding, but the person you're becoming is forged in the quiet moments when you show up anyway. Give "${title}" just 15 undistracted minutes today.`
  }
  return `Your journey toward "${title}" is a clean slate right now. You don't have to overhaul your whole routine—just take the very first step today.`
})

// Format Focus Time
function formatFocusTime(minutes) {
  if (!minutes || minutes <= 0) return '0 mins'
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  if (h > 0 && m > 0) return `${h}h ${m}m`
  if (h > 0) return `${h} hour${h > 1 ? 's' : ''}`
  return `${m} mins`
}

// Format Date
function formatMissionDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const today = new Date()
  const yesterday = new Date()
  yesterday.setDate(today.getDate() - 1)

  if (d.toDateString() === today.toDateString()) return 'Today'
  if (d.toDateString() === yesterday.toDateString()) return 'Yesterday'

  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

// Open Edit Goal Modal
function openEditModal() {
  if (!goalData.value) return
  goalForm.value = {
    title: goalData.value.title || '',
    description: goalData.value.description || '',
    category: goalData.value.category || 'Growth',
    timeframe: goalData.value.timeframe || 'long-term',
    priority: goalData.value.priority || 3,
    target_value: goalData.value.target_value,
    current_value: goalData.value.current_value || 0,
    unit: goalData.value.unit || '',
    status: goalData.value.status || 'active',
  }
  showEditModal.value = true
}

// Open Progress Update Modal
function openProgressModal() {
  if (!goalData.value || !goalData.value.target_value) return
  progressForm.value = {
    current_value: goalData.value.current_value || 0,
    increment: null,
    mode: 'increment',
  }
  showProgressModal.value = true
}

// Save Goal Edits
async function handleSaveGoal() {
  if (!goalForm.value.title.trim()) {
    auth.toastMessage('Please enter a goal title.', 'error')
    return
  }

  isSubmitting.value = true
  try {
    await goalService.updateGoal(goalData.value.id, goalForm.value)
    auth.toastMessage('Goal updated successfully!', 'success')
    showEditModal.value = false
    await fetchTrackingData(goalData.value.id)
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to save goal.'
    auth.toastMessage(msg, 'error')
  } finally {
    isSubmitting.value = false
  }
}

// Quick Action: Mark Goal as Achieved
async function handleMarkAchieved() {
  isSubmitting.value = true
  try {
    await goalService.updateGoal(goalData.value.id, { status: 'completed' })
    auth.toastMessage('🎉 Incredible! Milestone marked as Achieved!', 'success')
    showAchieveConfirmModal.value = false
    await fetchTrackingData(goalData.value.id)
  } catch (err) {
    auth.toastMessage('Failed to update goal status.', 'error')
  } finally {
    isSubmitting.value = false
  }
}

// Save Measurable Progress
async function handleSaveProgress() {
  if (!goalData.value) return

  isSubmitting.value = true
  try {
    const payload = progressForm.value.mode === 'increment'
      ? { increment: Number(progressForm.value.increment) || 0 }
      : { current_value: Number(progressForm.value.current_value) || 0 }

    const res = await goalService.updateProgress(goalData.value.id, payload)
    auth.toastMessage('Goal progress updated!', 'success')
    showProgressModal.value = false
    
    if (res.data?.data?.target?.is_target_reached) {
      auth.toastMessage('🏆 Target accomplished! Congratulations!', 'success')
    }
    
    await fetchTrackingData(goalData.value.id)
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to update progress.'
    auth.toastMessage(msg, 'error')
  } finally {
    isSubmitting.value = false
  }
}

// Start Next Chapter (Navigate to Onboarding to configure new goal & fears)
function startNextChapter() {
  router.push('/onboarding')
}

// Navigate to Daily Missions
function goToMissions() {
  router.push('/missions')
}

// Navigate to Chat with Context
function goToChat() {
  router.push('/chat')
}

onMounted(async () => {
  const goalIdParam = route.query.goal_id ? Number(route.query.goal_id) : null
  await fetchTrackingData(goalIdParam)
})

watch(() => route.query.goal_id, (newId) => {
  if (newId) {
    fetchTrackingData(Number(newId))
  }
})
</script>

<template>
  <div class="goal-tracking-page">
    <Navbar />

    <main class="main-container">
      <!-- ── LOADING STATE ──────────────────────── -->
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner"></div>
        <p class="loading-text">Calculating your Goal Momentum & insights...</p>
      </div>

      <!-- ── EMPTY STATE (No Goals Configured) ─── -->
      <div v-else-if="!goalData" class="empty-state-card">
        <div class="empty-icon-circle">🎯</div>
        <h2 class="empty-title">Your journey starts here</h2>
        <p class="empty-desc">
          Goal Tracking visualizes your consistency and action momentum over time.
          Define what you want to achieve, and let your Future Self guide your daily micro-missions.
        </p>
        <button class="btn-primary-glow" @click="startNextChapter">
          <span>+ Set Your Goal</span>
        </button>
      </div>

      <!-- ── MAIN GOAL TRACKING DASHBOARD ─────── -->
      <div v-else class="tracking-layout">

        <!-- ── GOAL ACHIEVED CELEBRATION HERO BANNER ── -->
        <section v-if="isGoalAchieved" class="achievement-banner-card">
          <div class="achievement-icon-wrap">🏆</div>
          <div class="achievement-body">
            <span class="achievement-tag">MILESTONE ACCOMPLISHED</span>
            <h2 class="achievement-title">You Achieved This Goal!</h2>
            <p class="achievement-desc">
              You completed {{ momentum?.missions_completed ?? 0 }} focused missions and invested {{ formatFocusTime(momentum?.focus_minutes) }} of dedicated action into <strong>"{{ goalData.title }}"</strong>.
            </p>
          </div>
          <div class="achievement-actions">
            <button class="btn-achievement-primary" @click="startNextChapter" id="btn-start-next-chapter">
              🚀 Embark on Your Next Chapter →
            </button>
          </div>
        </section>

        <!-- ── SECTION 1: GOAL HEADER ───────────── -->
        <header class="goal-header-card">
          <div class="header-top-row">
            <div class="header-left-meta">
              <span class="meta-badge category-badge">{{ goalData.category || 'General' }}</span>
              <span class="meta-badge timeframe-badge">
                {{ goalData.timeframe === 'short-term' ? '⚡ Short-term' : '🌠 Long-term' }}
              </span>
              <div class="priority-stars" :title="`Priority Level ${goalData.priority} of 5`">
                <span
                  v-for="star in 5"
                  :key="star"
                  class="star-icon"
                  :class="{ active: star <= (goalData.priority || 3) }"
                >★</span>
              </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="header-actions">
              <button
                v-if="completedPastGoals.length > 0"
                class="btn-past-goals"
                @click="showPastGoalsDrawer = !showPastGoalsDrawer"
                title="View past completed chapters"
              >
                📜 Past Chapters ({{ completedPastGoals.length }})
              </button>

              <button
                v-if="!isGoalAchieved"
                class="btn-mark-achieved"
                @click="showAchieveConfirmModal = true"
                title="Mark this goal as achieved"
                id="btn-mark-achieved"
              >
                🏆 Mark as Achieved
              </button>

              <button class="btn-action-ghost" @click="openEditModal" title="Edit goal details" id="btn-edit-goal">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                <span>Edit Goal</span>
              </button>
            </div>
          </div>

          <!-- Past Goals Drawer (If toggled) -->
          <div v-if="showPastGoalsDrawer" class="past-goals-drawer">
            <h4 class="past-goals-title">Archived Life Chapters:</h4>
            <div class="past-goals-list">
              <div
                v-for="pg in completedPastGoals"
                :key="pg.id"
                class="past-goal-chip"
                @click="fetchTrackingData(pg.id)"
              >
                <span>✓ {{ pg.title }}</span>
                <span class="chip-cat">{{ pg.category }}</span>
              </div>
            </div>
          </div>

          <div class="header-main-title">
            <h1 class="goal-title">{{ goalData.title }}</h1>
            <p v-if="goalData.description" class="goal-description">{{ goalData.description }}</p>
          </div>

          <!-- Measurable Target Strip (If applicable) -->
          <div v-if="goalData.target_value" class="measurable-progress-strip">
            <div class="measurable-info">
              <span class="measurable-label">🎯 Quantitative Target:</span>
              <strong class="measurable-numbers">
                {{ goalData.current_value || 0 }} / {{ goalData.target_value }} {{ goalData.unit || 'units' }}
              </strong>
              <span class="measurable-pct">({{ momentum?.target?.percentage || 0 }}%)</span>
            </div>
            <div class="measurable-bar-wrap">
              <div
                class="measurable-bar-fill"
                :style="{ width: `${Math.min(momentum?.target?.percentage || 0, 100)}%` }"
              ></div>
            </div>
            <button class="btn-log-progress" @click="openProgressModal" id="btn-log-progress">
              + Update Progress
            </button>
          </div>
        </header>

        <!-- ── SECTION 2: GOAL MOMENTUM HERO CARD ── -->
        <section class="momentum-hero-card" :class="statusConfig.haloClass">
          <div class="momentum-grid">
            <!-- Circular Gauge -->
            <div class="gauge-column">
              <div class="gauge-ring-wrapper">
                <svg class="gauge-svg" viewBox="0 0 180 180">
                  <circle
                    class="gauge-bg"
                    cx="90"
                    cy="90"
                    r="74"
                    stroke-width="12"
                  />
                  <circle
                    class="gauge-progress"
                    cx="90"
                    cy="90"
                    r="74"
                    stroke-width="12"
                    :stroke-dasharray="465"
                    :stroke-dashoffset="465 - (465 * (momentum?.score ?? 0)) / 100"
                    :class="statusConfig.colorClass"
                  />
                </svg>
                <div class="gauge-inner-content">
                  <span class="gauge-score-number">{{ momentum?.score ?? 0 }}</span>
                  <span class="gauge-score-max">/ 100</span>
                  <span class="gauge-label-tag">MOMENTUM</span>
                </div>
              </div>
            </div>

            <!-- Momentum Narrative & Sub-Scores -->
            <div class="momentum-details-column">
              <div class="status-pill-row">
                <span class="status-pill" :class="statusConfig.badgeClass">
                  <span class="status-dot"></span>
                  {{ statusConfig.label }}
                </span>
                <span class="trend-pill" :class="trendConfig.class" :title="trendConfig.text">
                  <span class="trend-icon">{{ trendConfig.icon }}</span>
                  {{ trendConfig.title }}
                </span>
              </div>

              <h2 class="momentum-headline">{{ statusConfig.headline }}</h2>
              <p class="momentum-description">{{ statusConfig.description }}</p>

              <!-- Educational Subtext -->
              <div class="momentum-disclaimer">
                <span class="info-icon">ℹ️</span>
                <span>
                  <strong>Goal Momentum</strong> measures action consistency, recency, and reflections over time—answering <em>"Am I moving toward this goal?"</em>
                </span>
              </div>

              <!-- 4 Modular Sub-Score Progress Bars -->
              <div class="subscores-grid">
                <div class="subscore-item">
                  <div class="subscore-header">
                    <span class="subscore-title">Mission Completion (40%)</span>
                    <span class="subscore-val">{{ momentum?.mission_completion_rate ?? 0 }}%</span>
                  </div>
                  <div class="subscore-bar">
                    <div class="subscore-fill" :style="{ width: `${momentum?.sub_scores?.mission_completion ?? 0}%` }"></div>
                  </div>
                </div>

                <div class="subscore-item">
                  <div class="subscore-header">
                    <span class="subscore-title">Consistency (30%)</span>
                    <span class="subscore-val">{{ momentum?.sub_scores?.consistency ?? 0 }}%</span>
                  </div>
                  <div class="subscore-bar">
                    <div class="subscore-fill" :style="{ width: `${momentum?.sub_scores?.consistency ?? 0}%` }"></div>
                  </div>
                </div>

                <div class="subscore-item">
                  <div class="subscore-header">
                    <span class="subscore-title">Recent Activity (20%)</span>
                    <span class="subscore-val">{{ momentum?.sub_scores?.recent_activity ?? 0 }}%</span>
                  </div>
                  <div class="subscore-bar">
                    <div class="subscore-fill" :style="{ width: `${momentum?.sub_scores?.recent_activity ?? 0}%` }"></div>
                  </div>
                </div>

                <div class="subscore-item">
                  <div class="subscore-header">
                    <span class="subscore-title">Reflections (10%)</span>
                    <span class="subscore-val">{{ momentum?.sub_scores?.check_in_reflection ?? 0 }}%</span>
                  </div>
                  <div class="subscore-bar">
                    <div class="subscore-fill" :style="{ width: `${momentum?.sub_scores?.check_in_reflection ?? 0}%` }"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- ── SECTION 3: YOUR WEEK (ACTIVITY & METRICS) ── -->
        <section class="section-card">
          <div class="section-header">
            <div>
              <span class="section-badge">WEEKLY PULSE</span>
              <h2 class="section-heading">Your Action Rhythm</h2>
            </div>
            <p class="section-subtitle">Real daily mission actions advancing this goal across the last 7 days</p>
          </div>

          <!-- 7-Day Visual Calendar Strip -->
          <div class="weekly-calendar-strip">
            <div
              v-for="day in momentum?.weekly_metrics || []"
              :key="day.date"
              class="day-pill"
              :class="{
                'day-completed': day.status === 'completed',
                'day-pending': day.status === 'pending',
                'day-skipped': day.status === 'skipped',
                'day-none': day.status === 'none',
              }"
              :title="day.title ? `${day.day_name}: ${day.title} (${day.status})` : `${day.day_name}: No mission logged`"
            >
              <span class="day-name">{{ day.day_name }}</span>
              <span class="day-date-number">{{ day.day_short }}</span>
              <div class="day-status-icon">
                <span v-if="day.status === 'completed'">✓</span>
                <span v-else-if="day.status === 'pending'">⏳</span>
                <span v-else-if="day.status === 'skipped'">✕</span>
                <span v-else class="empty-dot">·</span>
              </div>
              <span v-if="day.focus_minutes > 0" class="day-mins">{{ day.focus_minutes }}m</span>
            </div>
          </div>

          <!-- Summary Metric Cards -->
          <div class="metrics-grid">
            <div class="metric-card">
              <div class="metric-icon-wrap violet">🎯</div>
              <div class="metric-body">
                <span class="metric-label">Missions Completed</span>
                <div class="metric-value">
                  {{ momentum?.missions_completed ?? 0 }}
                  <span class="metric-total">/ {{ momentum?.missions_assigned ?? 0 }}</span>
                </div>
              </div>
            </div>

            <div class="metric-card">
              <div class="metric-icon-wrap orange">🔥</div>
              <div class="metric-body">
                <span class="metric-label">Goal Streak</span>
                <div class="metric-value">
                  {{ momentum?.current_streak ?? 0 }}
                  <span class="metric-unit">day{{ momentum?.current_streak === 1 ? '' : 's' }}</span>
                </div>
              </div>
            </div>

            <div class="metric-card">
              <div class="metric-icon-wrap blue">⏱️</div>
              <div class="metric-body">
                <span class="metric-label">Focus Time Invested</span>
                <div class="metric-value">
                  {{ formatFocusTime(momentum?.focus_minutes) }}
                </div>
              </div>
            </div>

            <div class="metric-card">
              <div class="metric-icon-wrap green">📊</div>
              <div class="metric-body">
                <span class="metric-label">Completion Rate</span>
                <div class="metric-value">
                  {{ momentum?.mission_completion_rate ?? 0 }}%
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- ── SECTION 4: PROGRESS TREND COMPARISON ── -->
        <section class="section-card">
          <div class="section-header-row">
            <div>
              <span class="section-badge">COMPARATIVE TRAJECTORY</span>
              <h2 class="section-heading">Activity Trend</h2>
            </div>
            <!-- Timeframe selector -->
            <div class="timeframe-toggle-group">
              <button
                class="timeframe-btn"
                :class="{ active: selectedTrendTimeframe === '7D' }"
                @click="selectedTrendTimeframe = '7D'"
              >
                7D
              </button>
              <button
                class="timeframe-btn"
                :class="{ active: selectedTrendTimeframe === '30D' }"
                @click="selectedTrendTimeframe = '30D'"
              >
                30D
              </button>
              <button
                class="timeframe-btn"
                :class="{ active: selectedTrendTimeframe === 'Overall' }"
                @click="selectedTrendTimeframe = 'Overall'"
              >
                Overall
              </button>
            </div>
          </div>

          <div class="trend-visual-card">
            <div class="trend-side-info">
              <div class="trend-indicator-badge" :class="trendConfig.class">
                <span class="trend-big-arrow">{{ trendConfig.icon }}</span>
                <div>
                  <h3 class="trend-status-title">{{ trendConfig.title }}</h3>
                  <p class="trend-status-desc">{{ trendConfig.text }}</p>
                </div>
              </div>
            </div>

            <!-- Comparison Bars -->
            <div class="trend-comparison-bars">
              <div class="comparison-row">
                <div class="comp-label">
                  <span>Current 7 Days</span>
                  <strong>{{ momentum?.trend_details?.current_period_completions ?? 0 }} actions</strong>
                </div>
                <div class="comp-track">
                  <div
                    class="comp-bar current-bar"
                    :style="{
                      width: `${Math.min(100, Math.max(10, ((momentum?.trend_details?.current_period_completions ?? 0) / 7) * 100))}%`
                    }"
                  ></div>
                </div>
              </div>

              <div class="comparison-row">
                <div class="comp-label">
                  <span>Previous 7 Days</span>
                  <strong>{{ momentum?.trend_details?.previous_period_completions ?? 0 }} actions</strong>
                </div>
                <div class="comp-track">
                  <div
                    class="comp-bar prev-bar"
                    :style="{
                      width: `${Math.min(100, Math.max(10, ((momentum?.trend_details?.previous_period_completions ?? 0) / 7) * 100))}%`
                    }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- ── SECTION 5: RECENT GOAL-RELATED ACTIONS ── -->
        <section class="section-card">
          <div class="section-header-row">
            <div>
              <span class="section-badge">ACTION LOG</span>
              <h2 class="section-heading">Recent Goal Actions</h2>
            </div>
            <router-link to="/missions" class="link-view-all">View All Missions →</router-link>
          </div>

          <div v-if="recentMissions.length === 0" class="no-missions-placeholder">
            <span class="empty-mini-icon">🌱</span>
            <h4>No actions recorded for this goal yet</h4>
            <p>Your Future Self will assign a tailored daily mission advancing this goal each morning.</p>
            <button class="btn-secondary-glow" @click="goToMissions">
              Open Daily Missions
            </button>
          </div>

          <div v-else class="missions-timeline">
            <div
              v-for="mission in recentMissions"
              :key="mission.id"
              class="mission-timeline-item"
              :class="`mission-status-${mission.status}`"
            >
              <div class="timeline-status-icon">
                <span v-if="mission.status === 'completed'">✓</span>
                <span v-else-if="mission.status === 'skipped'">✕</span>
                <span v-else>⏳</span>
              </div>

              <div class="mission-item-content">
                <div class="mission-item-header">
                  <h4 class="mission-item-title">{{ mission.title }}</h4>
                  <div class="mission-item-badges">
                    <span v-if="mission.category" class="tag-category">{{ mission.category }}</span>
                    <span class="tag-date">{{ formatMissionDate(mission.mission_date) }}</span>
                    <span class="tag-minutes">⏱️ {{ mission.estimated_minutes || 15 }}m</span>
                  </div>
                </div>

                <p class="mission-item-desc">{{ mission.description }}</p>

                <!-- Reflection quote if user wrote one -->
                <div v-if="mission.reflection" class="mission-reflection-quote">
                  <span class="quote-mark">“</span>
                  <p>{{ mission.reflection }}</p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- ── SECTION 6 & 7: FUTURE SELF INSIGHT & NEXT STEP ── -->
        <div class="bottom-actions-grid">
          <!-- Future Self Insight Placeholder Card -->
          <div class="future-self-insight-card">
            <div class="insight-header">
              <div class="insight-avatar">✨</div>
              <div>
                <span class="insight-tag">YOUR FUTURE SELF SAYS</span>
                <h3 class="insight-title">Perspective from Tomorrow</h3>
              </div>
            </div>
            <p class="insight-body">
              "{{ futureSelfInsight }}"
            </p>
            <button class="btn-chat-link" @click="goToChat" id="btn-chat-goal">
              💬 Discuss with Your Future Self
            </button>
          </div>

          <!-- Next Step Action Card -->
          <div class="next-step-card">
            <div class="next-step-badge">{{ isGoalAchieved ? 'NEW HORIZONS' : 'READY FOR ACTION' }}</div>
            <h3 class="next-step-title">{{ isGoalAchieved ? 'Ready for Your Next Summit?' : 'Your Next Step' }}</h3>
            <p class="next-step-desc">
              {{ isGoalAchieved 
                ? 'You conquered this goal. Embark on your next transformation with a new goal, new fears to overcome, and tailored missions.' 
                : 'Momentum is maintained one single focused action at a time. View today\'s personalized mission tailored to your energy.' 
              }}
            </p>
            <button
              v-if="isGoalAchieved"
              class="btn-primary-glow btn-block"
              @click="startNextChapter"
              id="btn-next-chapter-cta"
            >
              🚀 Embark on Next Goal →
            </button>
            <button
              v-else
              class="btn-primary-glow btn-block"
              @click="goToMissions"
              id="btn-view-today-mission"
            >
              ⚡ View Today's Mission →
            </button>
          </div>
        </div>

      </div>
    </main>

    <!-- ── MODAL: EDIT GOAL ─────────────────── -->
    <Transition name="modal-fade">
      <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
        <div class="modal-card">
          <div class="modal-header">
            <h3 class="modal-title">Edit Your Active Goal</h3>
            <button class="btn-close" @click="showEditModal = false">✕</button>
          </div>

          <form @submit.prevent="handleSaveGoal" class="modal-form">
            <div class="form-group">
              <label class="form-label">Goal Title <span class="req">*</span></label>
              <input
                v-model="goalForm.title"
                type="text"
                class="form-input"
                placeholder="e.g. Master Full-Stack Development"
                required
              />
            </div>

            <div class="form-group">
              <label class="form-label">Description & Motivation</label>
              <textarea
                v-model="goalForm.description"
                class="form-textarea"
                rows="3"
                placeholder="Why does this goal matter to your future self?"
              ></textarea>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Category</label>
                <select v-model="goalForm.category" class="form-select">
                  <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Timeframe</label>
                <select v-model="goalForm.timeframe" class="form-select">
                  <option value="short-term">⚡ Short-term (Weeks/Months)</option>
                  <option value="long-term">🌠 Long-term (1-5 Years)</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Priority (1-5)</label>
                <div class="priority-picker">
                  <button
                    v-for="p in 5"
                    :key="p"
                    type="button"
                    class="p-btn"
                    :class="{ active: goalForm.priority === p }"
                    @click="goalForm.priority = p"
                  >
                    ★ {{ p }}
                  </button>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Status</label>
                <select v-model="goalForm.status" class="form-select">
                  <option value="active">Active</option>
                  <option value="completed">Completed / Achieved 🏆</option>
                  <option value="dropped">Dropped</option>
                </select>
              </div>
            </div>

            <!-- Optional Measurable Target Fields -->
            <div class="form-section-divider">
              <span>Optional Measurable Target</span>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Target Number</label>
                <input
                  v-model.number="goalForm.target_value"
                  type="number"
                  step="any"
                  min="0"
                  class="form-input"
                  placeholder="e.g. 100"
                />
              </div>

              <div class="form-group">
                <label class="form-label">Unit of Measure</label>
                <input
                  v-model="goalForm.unit"
                  type="text"
                  class="form-input"
                  placeholder="e.g. km, books, clients"
                />
              </div>
            </div>

            <div class="modal-actions">
              <button
                type="button"
                class="btn-modal-cancel"
                @click="showEditModal = false"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="btn-modal-submit"
                :disabled="isSubmitting"
              >
                {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- ── MODAL: CONFIRM MARK ACHIEVED ─────── -->
    <Transition name="modal-fade">
      <div v-if="showAchieveConfirmModal" class="modal-overlay" @click.self="showAchieveConfirmModal = false">
        <div class="modal-card modal-card-achievement">
          <div class="achievement-modal-icon">🏆</div>
          <h3 class="modal-title text-center">Mark Goal as Achieved?</h3>
          <p class="modal-desc text-center">
            Celebrating this milestone will transition your current chapter to <strong>Achieved</strong>. You can then embark on your next life goal and define its fears and traits.
          </p>
          <div class="modal-actions justify-center">
            <button
              type="button"
              class="btn-modal-cancel"
              @click="showAchieveConfirmModal = false"
            >
              Keep Active
            </button>
            <button
              type="button"
              class="btn-achievement-primary"
              :disabled="isSubmitting"
              @click="handleMarkAchieved"
            >
              {{ isSubmitting ? 'Marking...' : 'Yes, I Achieved It! 🎉' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ── MODAL: UPDATE MEASURABLE PROGRESS ── -->
    <Transition name="modal-fade">
      <div v-if="showProgressModal" class="modal-overlay" @click.self="showProgressModal = false">
        <div class="modal-card">
          <div class="modal-header">
            <h3 class="modal-title">Update Target Progress</h3>
            <button class="btn-close" @click="showProgressModal = false">✕</button>
          </div>

          <form @submit.prevent="handleSaveProgress" class="modal-form">
            <p class="modal-subtitle">
              Goal: <strong>{{ goalData?.title }}</strong> (Target: {{ goalData?.target_value }} {{ goalData?.unit }})
            </p>

            <div class="progress-mode-toggle">
              <button
                type="button"
                class="mode-btn"
                :class="{ active: progressForm.mode === 'increment' }"
                @click="progressForm.mode = 'increment'"
              >
                + Add Increment
              </button>
              <button
                type="button"
                class="mode-btn"
                :class="{ active: progressForm.mode === 'set' }"
                @click="progressForm.mode = 'set'"
              >
                Set Total Value
              </button>
            </div>

            <div v-if="progressForm.mode === 'increment'" class="form-group">
              <label class="form-label">Add progress ({{ goalData?.unit || 'units' }})</label>
              <input
                v-model.number="progressForm.increment"
                type="number"
                step="any"
                class="form-input"
                placeholder="e.g. 5"
                required
                autofocus
              />
            </div>

            <div v-else class="form-group">
              <label class="form-label">New Total Current Value ({{ goalData?.unit || 'units' }})</label>
              <input
                v-model.number="progressForm.current_value"
                type="number"
                step="any"
                class="form-input"
                placeholder="e.g. 45"
                required
                autofocus
              />
            </div>

            <div class="modal-actions">
              <button
                type="button"
                class="btn-modal-cancel"
                @click="showProgressModal = false"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="btn-modal-submit"
                :disabled="isSubmitting"
              >
                {{ isSubmitting ? 'Saving...' : 'Save Progress' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <FooterSection />
  </div>
</template>

<style scoped>
/* ── PAGE SHELL ───────────────────────────────────── */
.goal-tracking-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.15) 0%, transparent 60%),
              radial-gradient(circle at 10% 40%, rgba(124, 58, 237, 0.1) 0%, transparent 50%),
              #0d0a1a;
  color: #ffffff;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

.main-container {
  flex: 1;
  max-width: 1160px;
  width: 100%;
  margin: 0 auto;
  padding: 32px 24px 80px;
  box-sizing: border-box;
}

/* ── LOADING & EMPTY STATES ───────────────────────── */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 50vh;
  gap: 20px;
}

.loading-spinner {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border: 3px solid rgba(167, 139, 250, 0.2);
  border-top-color: #8b5cf6;
  animation: spin 0.9s linear infinite;
}

.loading-text {
  color: rgba(255, 255, 255, 0.65);
  font-size: 15px;
}

.empty-state-card {
  max-width: 580px;
  margin: 60px auto;
  padding: 48px 36px;
  background: rgba(24, 18, 43, 0.7);
  border: 1px solid rgba(167, 139, 250, 0.25);
  border-radius: 24px;
  text-align: center;
  backdrop-filter: blur(24px);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4);
}

.empty-icon-circle {
  width: 72px;
  height: 72px;
  margin: 0 auto 20px;
  border-radius: 50%;
  background: rgba(139, 92, 246, 0.15);
  border: 1px solid rgba(167, 139, 250, 0.35);
  font-size: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-title {
  font-size: 26px;
  font-weight: 800;
  margin-bottom: 12px;
  background: linear-gradient(135deg, #ffffff 0%, #c4b5fd 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.empty-desc {
  font-size: 15px;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.65);
  margin-bottom: 28px;
}

/* ── TRACKING LAYOUT ──────────────────────────────── */
.tracking-layout {
  display: flex;
  flex-direction: column;
  gap: 28px;
}

/* ── ACHIEVEMENT BANNER ───────────────────────────── */
.achievement-banner-card {
  padding: 28px 32px;
  border-radius: 22px;
  background: linear-gradient(135deg, rgba(234, 179, 8, 0.18) 0%, rgba(124, 58, 237, 0.22) 100%);
  border: 1px solid rgba(251, 191, 36, 0.45);
  box-shadow: 0 8px 32px rgba(234, 179, 8, 0.2), inset 0 1px 2px rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  gap: 24px;
  flex-wrap: wrap;
}

.achievement-icon-wrap {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: rgba(251, 191, 36, 0.2);
  border: 1px solid rgba(251, 191, 36, 0.5);
  font-size: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.achievement-body {
  flex: 1;
  min-width: 260px;
}

.achievement-tag {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.12em;
  color: #fbbf24;
  text-transform: uppercase;
}

.achievement-title {
  font-size: 24px;
  font-weight: 900;
  color: #ffffff;
  margin: 2px 0 6px;
}

.achievement-desc {
  font-size: 14px;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.85);
}

.btn-achievement-primary {
  padding: 12px 22px;
  border-radius: 12px;
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%);
  border: 1px solid rgba(255, 255, 255, 0.4);
  color: #1a0f2e;
  font-size: 14px;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 4px 18px rgba(245, 158, 11, 0.4);
  transition: all 0.25s;
}

.btn-achievement-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(245, 158, 11, 0.6);
}

/* ── SECTION 1: GOAL HEADER ───────────────────────── */
.goal-header-card {
  padding: 28px 32px;
  background: rgba(26, 18, 48, 0.75);
  border: 1px solid rgba(167, 139, 250, 0.22);
  border-radius: 22px;
  backdrop-filter: blur(20px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
}

.header-top-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 18px;
}

.header-left-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.meta-badge {
  padding: 4px 12px;
  border-radius: 9999px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}

.category-badge {
  background: rgba(139, 92, 246, 0.18);
  border: 1px solid rgba(167, 139, 250, 0.35);
  color: #c4b5fd;
}

.timeframe-badge {
  background: rgba(249, 115, 22, 0.14);
  border: 1px solid rgba(249, 115, 22, 0.35);
  color: #fdba74;
}

.priority-stars {
  display: flex;
  gap: 2px;
}

.star-icon {
  color: rgba(255, 255, 255, 0.15);
  font-size: 15px;
}

.star-icon.active {
  color: #fbbf24;
  text-shadow: 0 0 6px rgba(251, 191, 36, 0.6);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-past-goals {
  padding: 8px 14px;
  background: rgba(139, 92, 246, 0.15);
  border: 1px solid rgba(167, 139, 250, 0.3);
  border-radius: 10px;
  color: #c4b5fd;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-past-goals:hover {
  background: rgba(139, 92, 246, 0.25);
  color: #ffffff;
}

.btn-mark-achieved {
  padding: 8px 14px;
  background: rgba(251, 191, 36, 0.14);
  border: 1px solid rgba(251, 191, 36, 0.35);
  border-radius: 10px;
  color: #fbbf24;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-mark-achieved:hover {
  background: rgba(251, 191, 36, 0.25);
  color: #ffffff;
  border-color: rgba(251, 191, 36, 0.6);
}

.past-goals-drawer {
  margin-bottom: 20px;
  padding: 14px 18px;
  border-radius: 12px;
  background: rgba(15, 12, 28, 0.7);
  border: 1px solid rgba(167, 139, 250, 0.2);
}

.past-goals-title {
  font-size: 12px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.6);
  margin-bottom: 8px;
}

.past-goals-list {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.past-goal-chip {
  padding: 6px 12px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.12);
  font-size: 12px;
  color: rgba(255, 255, 255, 0.85);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}

.past-goal-chip:hover {
  background: rgba(139, 92, 246, 0.2);
  border-color: rgba(167, 139, 250, 0.4);
  color: #ffffff;
}

.chip-cat {
  font-size: 10px;
  padding: 1px 6px;
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.1);
  color: #c4b5fd;
}

.btn-action-ghost {
  padding: 8px 14px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.85);
  font-size: 13px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action-ghost:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.25);
  color: #ffffff;
}

.goal-title {
  font-size: 32px;
  font-weight: 800;
  letter-spacing: -0.02em;
  line-height: 1.2;
  margin-bottom: 8px;
  background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 70%, #c4b5fd 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.goal-description {
  font-size: 15px;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.7);
  max-width: 820px;
}

/* Measurable Progress Strip */
.measurable-progress-strip {
  margin-top: 20px;
  padding: 16px 20px;
  border-radius: 14px;
  background: rgba(13, 10, 26, 0.6);
  border: 1px solid rgba(167, 139, 250, 0.18);
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.measurable-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.75);
}

.measurable-numbers {
  color: #38bdf8;
}

.measurable-pct {
  color: #a78bfa;
  font-weight: 700;
}

.measurable-bar-wrap {
  flex: 1;
  min-width: 140px;
  height: 8px;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.1);
  overflow: hidden;
}

.measurable-bar-fill {
  height: 100%;
  border-radius: 9999px;
  background: linear-gradient(90deg, #38bdf8, #818cf8);
  transition: width 0.6s ease;
}

.btn-log-progress {
  padding: 6px 12px;
  border-radius: 8px;
  background: rgba(56, 189, 248, 0.15);
  border: 1px solid rgba(56, 189, 248, 0.4);
  color: #7dd3fc;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-log-progress:hover {
  background: rgba(56, 189, 248, 0.25);
  color: #ffffff;
}

/* ── SECTION 2: MOMENTUM HERO CARD ────────────────── */
.momentum-hero-card {
  padding: 36px 36px;
  border-radius: 24px;
  background: linear-gradient(135deg, rgba(29, 20, 56, 0.9) 0%, rgba(17, 12, 34, 0.95) 100%);
  border: 1px solid rgba(167, 139, 250, 0.3);
  position: relative;
  overflow: hidden;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
}

.momentum-hero-card::before {
  content: '';
  position: absolute;
  top: -40%;
  left: -20%;
  width: 60%;
  height: 120%;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(139, 92, 246, 0.18) 0%, transparent 70%);
  pointer-events: none;
}

.halo-emerald {
  border-color: rgba(52, 211, 153, 0.35);
  box-shadow: 0 0 45px rgba(16, 185, 129, 0.15), inset 0 1px 2px rgba(255, 255, 255, 0.1);
}

.halo-amber {
  border-color: rgba(251, 191, 36, 0.35);
  box-shadow: 0 0 45px rgba(245, 158, 11, 0.15), inset 0 1px 2px rgba(255, 255, 255, 0.1);
}

.halo-rose {
  border-color: rgba(244, 114, 182, 0.35);
  box-shadow: 0 0 45px rgba(244, 63, 94, 0.12), inset 0 1px 2px rgba(255, 255, 255, 0.1);
}

.momentum-grid {
  display: grid;
  grid-template-columns: 200px 1fr;
  gap: 36px;
  align-items: center;
  position: relative;
  z-index: 1;
}

.gauge-column {
  display: flex;
  justify-content: center;
}

.gauge-ring-wrapper {
  position: relative;
  width: 180px;
  height: 180px;
}

.gauge-svg {
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
}

.gauge-bg {
  fill: none;
  stroke: rgba(255, 255, 255, 0.08);
}

.gauge-progress {
  fill: none;
  stroke-linecap: round;
  transition: stroke-dashoffset 1s ease-out;
}

.color-emerald { stroke: #10b981; }
.color-amber { stroke: #f59e0b; }
.color-rose { stroke: #ec4899; }

.gauge-inner-content {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.gauge-score-number {
  font-size: 48px;
  font-weight: 900;
  letter-spacing: -0.03em;
  line-height: 1;
  background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.gauge-score-max {
  font-size: 13px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.4);
  margin-top: 2px;
}

.gauge-label-tag {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.14em;
  color: #a78bfa;
  margin-top: 4px;
}

.status-pill-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 12px;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 14px;
  border-radius: 9999px;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.02em;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: currentColor;
}

.badge-strong {
  background: rgba(16, 185, 129, 0.16);
  border: 1px solid rgba(52, 211, 153, 0.4);
  color: #34d399;
}

.badge-attention {
  background: rgba(245, 158, 11, 0.16);
  border: 1px solid rgba(251, 191, 36, 0.4);
  color: #fbbf24;
}

.badge-risk {
  background: rgba(244, 63, 94, 0.16);
  border: 1px solid rgba(251, 113, 133, 0.4);
  color: #fb7185;
}

.badge-achieved {
  background: rgba(139, 92, 246, 0.2);
  border: 1px solid rgba(196, 181, 253, 0.5);
  color: #c4b5fd;
}

.trend-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 12px;
  border-radius: 9999px;
  font-size: 12px;
  font-weight: 700;
}

.trend-improving {
  background: rgba(52, 211, 153, 0.12);
  border: 1px solid rgba(52, 211, 153, 0.3);
  color: #6ee7b7;
}

.trend-declining {
  background: rgba(251, 113, 133, 0.12);
  border: 1px solid rgba(251, 113, 133, 0.3);
  color: #fda4af;
}

.trend-stable {
  background: rgba(148, 163, 184, 0.12);
  border: 1px solid rgba(148, 163, 184, 0.3);
  color: #cbd5e1;
}

.momentum-headline {
  font-size: 22px;
  font-weight: 800;
  margin-bottom: 6px;
  color: #ffffff;
}

.momentum-description {
  font-size: 14px;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.75);
  margin-bottom: 16px;
}

.momentum-disclaimer {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 10px;
  background: rgba(15, 12, 26, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 12px;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.6);
  margin-bottom: 22px;
}

/* Sub-scores Grid */
.subscores-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px 20px;
}

.subscore-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.subscore-header {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
}

.subscore-val {
  font-weight: 700;
  color: #c4b5fd;
}

.subscore-bar {
  height: 6px;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.08);
  overflow: hidden;
}

.subscore-fill {
  height: 100%;
  border-radius: 9999px;
  background: linear-gradient(90deg, #7c3aed, #818cf8);
  transition: width 0.5s ease;
}

/* ── GENERIC SECTION CARD ─────────────────────────── */
.section-card {
  padding: 28px 32px;
  border-radius: 22px;
  background: rgba(22, 16, 40, 0.7);
  border: 1px solid rgba(167, 139, 250, 0.2);
  backdrop-filter: blur(16px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
}

.section-header {
  margin-bottom: 22px;
}

.section-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 22px;
}

.section-badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #a78bfa;
  margin-bottom: 4px;
}

.section-heading {
  font-size: 20px;
  font-weight: 800;
  color: #ffffff;
}

.section-subtitle {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.6);
  margin-top: 2px;
}

.link-view-all {
  font-size: 13px;
  font-weight: 700;
  color: #a78bfa;
  text-decoration: none;
  transition: color 0.2s;
}

.link-view-all:hover {
  color: #c4b5fd;
  text-decoration: underline;
}

/* ── SECTION 3: WEEKLY STRIP & METRICS ────────────── */
.weekly-calendar-strip {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 10px;
  margin-bottom: 24px;
}

.day-pill {
  padding: 14px 8px;
  border-radius: 14px;
  background: rgba(15, 12, 28, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  text-align: center;
  transition: all 0.2s;
}

.day-pill:hover {
  transform: translateY(-2px);
  border-color: rgba(167, 139, 250, 0.4);
}

.day-name {
  font-size: 11px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.5);
  text-transform: uppercase;
}

.day-date-number {
  font-size: 13px;
  font-weight: 800;
  color: #ffffff;
}

.day-status-icon {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 800;
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.3);
}

.day-completed {
  background: rgba(16, 185, 129, 0.1);
  border-color: rgba(52, 211, 153, 0.35);
}

.day-completed .day-status-icon {
  background: #10b981;
  color: #ffffff;
  box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
}

.day-pending {
  border-color: rgba(251, 191, 36, 0.35);
}

.day-pending .day-status-icon {
  background: rgba(251, 191, 36, 0.2);
  color: #fbbf24;
}

.day-skipped {
  opacity: 0.6;
}

.day-mins {
  font-size: 10px;
  font-weight: 700;
  color: #6ee7b7;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

.metric-card {
  padding: 16px 18px;
  border-radius: 16px;
  background: rgba(15, 12, 28, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.07);
  display: flex;
  align-items: center;
  gap: 14px;
}

.metric-icon-wrap {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.metric-icon-wrap.violet { background: rgba(139, 92, 246, 0.2); }
.metric-icon-wrap.orange { background: rgba(249, 115, 22, 0.2); }
.metric-icon-wrap.blue { background: rgba(56, 189, 248, 0.2); }
.metric-icon-wrap.green { background: rgba(16, 185, 129, 0.2); }

.metric-body {
  display: flex;
  flex-direction: column;
}

.metric-label {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.55);
}

.metric-value {
  font-size: 20px;
  font-weight: 800;
  color: #ffffff;
  margin-top: 2px;
}

.metric-total, .metric-unit {
  font-size: 13px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.45);
}

/* ── SECTION 4: PROGRESS TREND ────────────────────── */
.timeframe-toggle-group {
  display: flex;
  gap: 4px;
  padding: 3px;
  background: rgba(15, 12, 28, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
}

.timeframe-btn {
  padding: 5px 12px;
  border-radius: 7px;
  font-size: 12px;
  font-weight: 700;
  background: transparent;
  color: rgba(255, 255, 255, 0.6);
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.timeframe-btn.active {
  background: #7c3aed;
  color: #ffffff;
}

.trend-visual-card {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  padding: 20px;
  border-radius: 16px;
  background: rgba(15, 12, 28, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.trend-indicator-badge {
  padding: 16px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  gap: 16px;
}

.trend-big-arrow {
  font-size: 32px;
  font-weight: 900;
}

.trend-status-title {
  font-size: 16px;
  font-weight: 800;
  margin-bottom: 4px;
}

.trend-status-desc {
  font-size: 13px;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.8);
}

.trend-comparison-bars {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 14px;
}

.comparison-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.comp-label {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
}

.comp-track {
  height: 8px;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.08);
  overflow: hidden;
}

.comp-bar {
  height: 100%;
  border-radius: 9999px;
  transition: width 0.6s ease;
}

.current-bar { background: linear-gradient(90deg, #7c3aed, #a78bfa); }
.prev-bar { background: linear-gradient(90deg, #475569, #64748b); }

/* ── SECTION 5: ACTION LOG ────────────────────────── */
.no-missions-placeholder {
  text-align: center;
  padding: 40px 20px;
}

.empty-mini-icon {
  font-size: 32px;
  margin-bottom: 10px;
  display: block;
}

.no-missions-placeholder h4 {
  font-size: 16px;
  font-weight: 700;
  margin-bottom: 6px;
}

.no-missions-placeholder p {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.6);
  margin-bottom: 20px;
}

.missions-timeline {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.mission-timeline-item {
  padding: 16px 20px;
  border-radius: 14px;
  background: rgba(15, 12, 28, 0.55);
  border: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  align-items: flex-start;
  gap: 16px;
  transition: all 0.2s;
}

.mission-timeline-item:hover {
  background: rgba(26, 20, 48, 0.7);
  border-color: rgba(167, 139, 250, 0.25);
}

.timeline-status-icon {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 800;
  flex-shrink: 0;
  margin-top: 2px;
}

.mission-status-completed .timeline-status-icon {
  background: rgba(16, 185, 129, 0.2);
  color: #34d399;
  border: 1px solid rgba(52, 211, 153, 0.4);
}

.mission-status-pending .timeline-status-icon {
  background: rgba(251, 191, 36, 0.2);
  color: #fbbf24;
  border: 1px solid rgba(251, 191, 36, 0.4);
}

.mission-status-skipped .timeline-status-icon {
  background: rgba(148, 163, 184, 0.2);
  color: #94a3b8;
  border: 1px solid rgba(148, 163, 184, 0.4);
}

.mission-item-content {
  flex: 1;
}

.mission-item-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 4px;
}

.mission-item-title {
  font-size: 15px;
  font-weight: 700;
  color: #ffffff;
}

.mission-item-badges {
  display: flex;
  align-items: center;
  gap: 8px;
}

.tag-category {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
  background: rgba(139, 92, 246, 0.15);
  color: #c4b5fd;
}

.tag-date, .tag-minutes {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.5);
}

.mission-item-desc {
  font-size: 13px;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.7);
}

.mission-reflection-quote {
  margin-top: 10px;
  padding: 8px 12px;
  border-radius: 8px;
  background: rgba(139, 92, 246, 0.08);
  border-left: 3px solid #8b5cf6;
  font-size: 12px;
  color: #e2e8f0;
  display: flex;
  gap: 6px;
}

.quote-mark {
  color: #8b5cf6;
  font-weight: 800;
}

/* ── BOTTOM ACTIONS (INSIGHT & NEXT STEP) ─────────── */
.bottom-actions-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.future-self-insight-card {
  padding: 28px;
  border-radius: 22px;
  background: linear-gradient(135deg, rgba(30, 20, 58, 0.85) 0%, rgba(18, 13, 36, 0.9) 100%);
  border: 1px solid rgba(167, 139, 250, 0.25);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 16px;
}

.insight-header {
  display: flex;
  align-items: center;
  gap: 12px;
}

.insight-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #7c3aed, #3b82f6);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  box-shadow: 0 0 14px rgba(124, 58, 237, 0.5);
}

.insight-tag {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.12em;
  color: #c4b5fd;
}

.insight-title {
  font-size: 17px;
  font-weight: 800;
  color: #ffffff;
}

.insight-body {
  font-size: 14px;
  line-height: 1.65;
  color: rgba(255, 255, 255, 0.85);
  font-style: italic;
}

.btn-chat-link {
  align-self: flex-start;
  padding: 8px 16px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #ffffff;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-chat-link:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(167, 139, 250, 0.4);
}

.next-step-card {
  padding: 28px;
  border-radius: 22px;
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.12) 0%, rgba(124, 58, 237, 0.15) 100%);
  border: 1px solid rgba(249, 115, 22, 0.3);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 16px;
}

.next-step-badge {
  display: inline-block;
  align-self: flex-start;
  padding: 4px 10px;
  border-radius: 6px;
  background: rgba(249, 115, 22, 0.2);
  color: #fb923c;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.12em;
}

.next-step-title {
  font-size: 22px;
  font-weight: 800;
  color: #ffffff;
}

.next-step-desc {
  font-size: 14px;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.75);
}

.btn-primary-glow {
  padding: 12px 24px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 800;
  color: #ffffff;
  background: linear-gradient(135deg, #f97316 0%, #7c3aed 100%);
  border: 1px solid rgba(253, 186, 116, 0.5);
  box-shadow: 0 4px 20px rgba(249, 115, 22, 0.35);
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-primary-glow:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 26px rgba(249, 115, 22, 0.5);
}

.btn-secondary-glow {
  padding: 10px 20px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 700;
  color: #ffffff;
  background: rgba(124, 58, 237, 0.3);
  border: 1px solid rgba(167, 139, 250, 0.4);
  cursor: pointer;
}

.btn-block {
  width: 100%;
}

/* ── MODALS ───────────────────────────────────────── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(8px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-card {
  width: 100%;
  max-width: 520px;
  background: #191232;
  border: 1px solid rgba(167, 139, 250, 0.3);
  border-radius: 20px;
  padding: 28px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
}

.modal-card-achievement {
  max-width: 440px;
  text-align: center;
}

.achievement-modal-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  border-radius: 50%;
  background: rgba(251, 191, 36, 0.2);
  border: 1px solid rgba(251, 191, 36, 0.5);
  font-size: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-desc {
  font-size: 14px;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.75);
  margin-bottom: 24px;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.modal-title {
  font-size: 20px;
  font-weight: 800;
  color: #ffffff;
}

.btn-close {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.6);
  border: none;
  font-size: 14px;
  cursor: pointer;
}

.modal-subtitle {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 16px;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1;
}

.form-row {
  display: flex;
  gap: 12px;
}

.form-label {
  font-size: 12px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.75);
}

.form-label .req { color: #f87171; }

.form-input, .form-textarea, .form-select {
  width: 100%;
  padding: 10px 14px;
  background: rgba(10, 8, 20, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 10px;
  color: #ffffff;
  font-size: 14px;
  box-sizing: border-box;
  outline: none;
  transition: border-color 0.2s;
}

.form-input:focus, .form-textarea:focus, .form-select:focus {
  border-color: #8b5cf6;
}

.priority-picker {
  display: flex;
  gap: 4px;
}

.p-btn {
  flex: 1;
  padding: 8px 4px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.6);
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
}

.p-btn.active {
  background: rgba(251, 191, 36, 0.2);
  border-color: #fbbf24;
  color: #fbbf24;
}

.form-section-divider {
  border-top: 1px dashed rgba(255, 255, 255, 0.12);
  margin: 6px 0;
  position: relative;
  text-align: center;
}

.form-section-divider span {
  position: relative;
  top: -9px;
  background: #191232;
  padding: 0 10px;
  font-size: 11px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.4);
}

.progress-mode-toggle {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
}

.mode-btn {
  flex: 1;
  padding: 8px 12px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.7);
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

.mode-btn.active {
  background: rgba(56, 189, 248, 0.2);
  border-color: #38bdf8;
  color: #38bdf8;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}

.modal-actions.justify-center {
  justify-content: center;
}

.btn-modal-cancel {
  padding: 10px 18px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.08);
  border: none;
  color: rgba(255, 255, 255, 0.7);
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
}

.btn-modal-submit {
  padding: 10px 22px;
  border-radius: 10px;
  background: linear-gradient(135deg, #7c3aed, #6366f1);
  border: none;
  color: #ffffff;
  font-size: 13px;
  font-weight: 800;
  cursor: pointer;
}

.btn-modal-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* ── ANIMATIONS ───────────────────────────────────── */
@keyframes spin {
  to { transform: rotate(360deg); }
}

.modal-fade-enter-active, .modal-fade-leave-active {
  transition: opacity 0.25s ease;
}

.modal-fade-enter-from, .modal-fade-leave-to {
  opacity: 0;
}

/* ── RESPONSIVE MEDIA QUERIES ─────────────────────── */
@media (max-width: 900px) {
  .momentum-grid {
    grid-template-columns: 1fr;
    text-align: center;
    gap: 24px;
  }

  .gauge-column {
    margin-bottom: 8px;
  }

  .status-pill-row {
    justify-content: center;
  }

  .subscores-grid {
    grid-template-columns: 1fr;
  }

  .metrics-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .trend-visual-card {
    grid-template-columns: 1fr;
  }

  .bottom-actions-grid {
    grid-template-columns: 1fr;
  }

  .achievement-banner-card {
    flex-direction: column;
    text-align: center;
  }

  .achievement-icon-wrap {
    margin: 0 auto;
  }
}

@media (max-width: 640px) {
  .main-container {
    padding: 16px 14px 60px;
  }

  .goal-header-card, .momentum-hero-card, .section-card, .future-self-insight-card, .next-step-card, .achievement-banner-card {
    padding: 20px 16px;
    border-radius: 18px;
  }

  .goal-title {
    font-size: 24px;
  }

  .weekly-calendar-strip {
    grid-template-columns: repeat(4, 1fr);
    gap: 6px;
  }

  .metrics-grid {
    grid-template-columns: 1fr;
  }

  .form-row {
    flex-direction: column;
  }
}
</style>
