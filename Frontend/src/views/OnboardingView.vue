<template>
  <div class="onboarding-container">
    <!-- NAV -->
    <nav>
      <div class="logo">
        <div class="logo-icon">✨</div>
        <span class="logo-name">FutureYou</span>
      </div>
      <div class="step-pill" v-if="currentStepNumber < 6">
        Setting up your profile &nbsp;·&nbsp; Step <strong>{{ Math.min(currentStepNumber + 1, 5) }}</strong> of 5
      </div>
      <div class="nav-actions">
        <button class="logout-btn" type="button" @click="handleLogout">Log out</button>
      </div>
    </nav>

    <!-- PROGRESS -->
    <div class="progress-wrap" v-if="currentStepNumber < 6">
      <div class="progress-inner">
        <div class="progress-labels">
          <div
            v-for="(label, i) in progressLabels"
            :key="i"
            class="progress-label"
            :class="{ done: i < currentStepNumber, active: i === currentStepNumber && currentStepNumber < 5 }"
          >
            {{ label }}
          </div>
        </div>
        <div class="progress-segs">
          <div
            v-for="i in 5"
            :key="i"
            class="progress-seg"
            :class="{ done: i - 1 < currentStepNumber, active: i - 1 === currentStepNumber && currentStepNumber < 5 }"
          ></div>
        </div>
      </div>
    </div>

    <!-- MAIN -->
    <main>
      <div class="card">
        <!-- STEP 1: GOALS -->
        <div v-show="currentStep === 0" :class="['step', { active: currentStep === 0 }]">
          <p class="step-label" style="color: var(--violet-light)">Step 1 of 5</p>
          <h1 class="step-title">Your Goals</h1>
          <p class="step-sub">What are you working towards?
             <!-- Add as many as you like — big or small. -->
            </p>

          <div class="saved-list">
            <div v-for="goal in goals" :key="goal.id" class="saved-goal">
              <div class="saved-body">
                <div class="saved-row">
                  <span class="saved-ttl">{{ goal.title }}</span>
                  <span class="badge" :class="goal.tf === 'short-term' ? 'badge-short' : 'badge-long'">
                    {{ goal.tf === 'short-term' ? 'short-term' : 'long-term' }}
                  </span>
                  <span v-if="goal.cat" class="badge" style="background: rgba(167, 139, 250, 0.12); color: #a78bfa">
                    {{ goal.cat }}
                  </span>
                </div>
                <div v-if="goal.desc" class="saved-desc">{{ goal.desc }}</div>
                <div class="saved-meta">
                  <div style="display: flex; gap: 3px">
                    <span v-for="n in 5" :key="n" class="star-mini" :style="{ color: n <= goal.prio ? '#fbbf24' : 'rgba(255,255,255,0.1)' }">
                      ★
                    </span>
                  </div>
                </div>
              </div>
              <button class="btn-rm" @click="removeDetail(goal.id, 'goals')">×</button>
            </div>
          </div>

          <!-- Panel -->
          <div v-show="showGoalPanel" class="panel panel-v">
            <div class="panel-hd">New Goal</div>
            <div class="field">
              <!-- <div> -->
                <label class="lbl">Title <span>*</span></label>
                <input v-model="newGoal.title" type="text" placeholder="e.g. Build my own company"/>
              <!-- </div> -->
              <!-- <div>
                <label class="lbl">Category</label>
                <select v-model="newGoal.cat" style="width: 100%; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.09); border-radius: 12px; padding: 12px 16px; font-size: 14px; font-family: Inter, sans-serif; color: rgba(255,255,255,.6); outline: none; cursor: pointer; appearance: none">
                  <option value="">— Select category —</option>
                  <option v-for="cat in goalCategories" :key="cat" :value="cat">{{ cat }}</option>
                </select>
              </div> -->
            </div>
            <div class="field">
              <label class="lbl">Description</label>
              <textarea v-model="newGoal.desc" rows="2" placeholder="What does achieving this mean to you?"></textarea>
            </div>
            <div class="field-row">
              <div>
                <label class="lbl">Timeframe</label>
                <div class="tf-row">
                  <button class="tf-btn" :class="{ short: newGoal.tf === 'short-term' }" @click="newGoal.tf = 'short-term'">
                    ⚡ Short-term
                  </button>
                  <button class="tf-btn" :class="{ long: newGoal.tf === 'long-term' }" @click="newGoal.tf = 'long-term'">
                    🌠 Long-term
                  </button>
                </div>
              </div>
              <div>
                <label class="lbl">Priority</label>
                <div class="stars">
                  <button
                    v-for="n in 5"
                    :key="n"
                    class="star"
                    :class="{ on: n <= newGoal.prio }"
                    @click="newGoal.prio = n"
                  >
                    ★
                  </button>
                </div>
              </div>
            </div>
            <div class="field" style="margin-top: -4px; margin-bottom: 0">
              <label class="lbl">Category tags</label>
              <div class="chip-group">
                <button
                  v-for="cat in goalCategories"
                  :key="cat"
                  class="chip"
                  :class="{ violet: newGoal.cat === cat }"
                  @click="newGoal.cat = newGoal.cat === cat ? '' : cat"
                >
                  {{ cat }}
                </button>
                <input hidden v-model="newGoal.cat" name="category" type="text" />
              </div>
            </div>
            <div class="divider"></div>
            <div class="panel-foot">
              <button class="btn btn-dim" @click="addGoal()" style="flex: 1">Add Goal</button>
              <button v-if="goals.length" class="btn btn-ghost" @click="showGoalPanel = false">Cancel</button>
            </div>
          </div>

          <!-- <button v-if="goals.length && !showGoalPanel" class="btn-add-more btn-add-v" @click="showGoalPanel = true">
            + Add another goal
          </button> -->
          <button
            class="btn-continue"
            :class="goals.length ? 'btn-v' : 'btn-dim'"
            :disabled="!goals.length"
            :style="{ boxShadow: goals.length ? '0 8px 32px rgba(124,58,237,.35)' : 'none' }"
            @click="goNext()"
          >
            Continue →
          </button>
        </div>

        <!-- STEP 2: FEARS -->
        <div v-show="currentStep === 1" :class="['step', { active: currentStep === 1 }]">
          <button class="btn btn-ghost back-btn" type="button" @click="goBack()">← Back</button>
          <p class="step-label" style="color: var(--pink)">Step 2 of 5</p>
          <h1 class="step-title">Your Fears</h1>
          <p class="step-sub">Naming them is the first step to overcoming them.</p>

          <div class="saved-list">
            <div v-for="fear in fears" :key="fear.id" class="saved-fear">
              <div class="saved-body">
                <div class="saved-row">
                  <span class="saved-ttl">{{ fear.text }}</span>
                  <span v-if="fear.cat" class="badge badge-pink">{{ fear.cat }}</span>
                </div>
                <div class="saved-meta">
                  <span style="font-size: 12px; color: rgba(255,255,255,.3)">Intensity:</span>
                  <div style="display: flex; gap: 6px">
                    <div v-for="n in 5" :key="n" style="width: 16px; height: 8px; border-radius: 99px;" :style="{ background: n <= fear.int ? '#f472b6' : 'rgba(255,255,255,0.08)' }"></div>
                  </div>
                  <span style="font-size: 12px; color: #f472b6">{{ fear.int }}/5</span>
                </div>
              </div>
              <button class="btn-rm" @click="removeDetail(fear.id, 'fears')">×</button>
            </div>
          </div>

          <div v-show="showFearPanel" class="panel panel-p">
            <div class="panel-hd">New Fear</div>
            <div class="field">
              <label class="lbl">What are you afraid of? <span>*</span></label>
              <textarea v-model="newFear.text" rows="2" placeholder="e.g. I'm afraid I'll never be good enough..."></textarea>
            </div>
            <div class="field">
              <label class="lbl">Category</label>
              <div class="chip-group">
                <button
                  v-for="cat in fearCategories"
                  :key="cat"
                  class="chip"
                  :class="{ pink: newFear.cat === cat }"
                  @click="newFear.cat = newFear.cat === cat ? '' : cat"
                >
                  {{ cat }}
                </button>
              </div>
            </div>
            <div class="field">
              <label class="lbl">Intensity</label>
              <div class="intensity-row">
                <div class="int-bars">
                  <div
                    v-for="n in 5"
                    :key="n"
                    class="int-seg"
                    :class="{ on: n <= newFear.int }"
                    @click="newFear.int = n; updateIntensityLabel()"
                  ></div>
                </div>
                <span class="int-val">{{ newFear.int }}/5</span>
              </div>
            </div>
            <div class="divider"></div>
            <div class="panel-foot">
              <button class="btn btn-dim" @click="addFear()" style="flex: 1">Add Fear</button>
              <button v-if="fears.length" class="btn btn-ghost" @click="showFearPanel = false">Cancel</button>
            </div>
          </div>

          <!-- <button v-if="fears.length && !showFearPanel" class="btn-add-more btn-add-p" @click="showFearPanel = true">
            + Add another fear
          </button> -->
          <button
            class="btn-continue"
            :class="fears.length ? 'btn-p' : 'btn-dim'"
            :disabled="!fears.length"
            :style="{ boxShadow: fears.length ? '0 8px 32px rgba(190,24,93,.35)' : 'none' }"
            @click="goNext()"
          >
            Continue →
          </button>
        </div>

        <!-- STEP 3: TRAITS -->
        <div v-show="currentStep === 2" :class="['step', { active: currentStep === 2 }]">
          <button class="btn btn-ghost back-btn" type="button" @click="goBack()">← Back</button>
          <p class="step-label" style="color: var(--amber)">Step 3 of 5</p>
          <h1 class="step-title">Desired Traits</h1>
          <p class="step-sub">Who do you want to become? Pick from the presets or add your own.</p>

          <div class="chip-group" style="margin-bottom: 24px; gap: 10px">
            <button
              v-for="trait in traitOptions"
              :key="trait"
              class="trait-chip"
              :class="{ on: selectedTraits.has(trait) }"
              @click="toggleTrait(trait)"
            >
              {{ selectedTraits.has(trait) ? '✓ ' : '' }}{{ trait }}
            </button>
          </div>

          <div class="divider"></div>

          <div class="field" style="margin-top: 20px">
            <label class="lbl">Add a custom trait</label>
            <div class="inline-add">
              <input
                v-model="customTraitInput"
                type="text"
                placeholder="e.g. Stoic, Visionary, Gritty..."
                @keydown.enter="addCustomTrait()"
              />
              <button class="btn btn-a" @click="addCustomTrait()" style="flex-shrink: 0">Add</button>
            </div>
            <div class="chip-group" style="margin-top: 12px">
              <span
                v-for="trait in customTraits"
                :key="trait"
                class="custom-tag"
              >
                {{ trait }}
                <button @click="removeDetail(trait.id, 'customTraits')">×</button>
              </span>
            </div>
          </div>

          <p v-if="selectedTraits.size > 0" class="trait-count">{{ selectedTraits.size }} trait{{ selectedTraits.size !== 1 ? 's' : '' }} selected</p>
          <button
            class="btn-continue"
            :class="selectedTraits.size ? 'btn-a' : 'btn-dim'"
            :disabled="!selectedTraits.size"
            :style="{ boxShadow: selectedTraits.size ? '0 8px 32px rgba(217,119,6,.35)' : 'none' }"
            @click="goNext()"
          >
            Continue →
          </button>
        </div>

        <!-- STEP 4: ROLE MODELS -->
        <div v-show="currentStep === 3" :class="['step', { active: currentStep === 3 }]">
          <button class="btn btn-ghost back-btn" type="button" @click="goBack()">← Back</button>
          <p class="step-label" style="color: var(--blue)">Step 4 of 5</p>
          <h1 class="step-title">Role Models</h1>
          <p class="step-sub">Who inspires you? Your Future Self will channel their mindset.</p>

          <div class="inline-add" style="margin-bottom: 20px">
            <input
              v-model="roleModelInput"
              type="text"
              placeholder="e.g. Kobe Bryant, Marcus Aurelius..."
              style="border-color: rgba(96,165,250,.2)"
              @keydown.enter="addRoleModel()"
            />
            <button class="btn btn-b" @click="addRoleModel()" style="flex-shrink: 0">Add</button>
          </div>

          <div style="margin-bottom: 24px">
            <p class="sugg-label">Suggestions — click to add</p>
            <div class="chip-group">
              <button
                v-for="sugg in roleModelSuggestions.filter(s => !roleModels.some(r => r.name === s))"
                :key="sugg"
                class="sugg-chip chip"
                @click="addRoleModelFromSugg(sugg)"
              >
                + {{ sugg }}
              </button>
            </div>
          </div>

          <div v-if="roleModels.length" style="margin-bottom: 24px">
            <p class="sugg-label">Your inspirations</p>
            <div class="saved-list">
              <div v-for="(role, i) in roleModels" :key="role.id" class="role-row">
                <div class="role-num">{{ i + 1 }}</div>
                <span class="role-name">{{ role.name }}</span>
                <button class="btn-rm" v-if="id" @click="removeDetail(role.id, 'role-models')">×</button>
                <button class="btn-rm" v-else @click="removeRole(role.name)">×</button>
              </div>
            </div>
          </div>

          <button
            class="btn-continue"
            :class="roleModels.length ? 'btn-b' : 'btn-dim'"
            :disabled="!roleModels.length"
            :style="{ boxShadow: roleModels.length ? '0 8px 32px rgba(37,99,235,.35)' : 'none' }"
            @click="goNext()"
          >
            Continue →
          </button>
          <button class="btn-skip" @click="goNext()">Skip for now</button>
        </div>

        <!-- STEP 5: TONE -->
        <div v-show="currentStep === 4" :class="['step', { active: currentStep === 4 }]">
          <button class="btn btn-ghost back-btn" type="button" @click="goBack()">← Back</button>
          <p class="step-label" style="color: var(--violet-light)">Step 5 of 5</p>
          <h1 class="step-title">How should Future You <span style="color: var(--violet-light)">talk to you?</span></h1>
          <p class="step-sub">Choose the voice that moves you most. You can change this anytime.</p>

          <div class="tone-grid">
            <button
              v-for="tone in tones"
              :key="tone.key"
              class="tone-card"
              :class="{ selected: selectedTone === tone.key }"
              @click="selectTone(tone.key)"
            >
              <div class="tone-icon">{{ tone.icon }}</div>
              <div class="tone-info">
                <div class="tone-name">{{ tone.label }}</div>
                <div class="tone-desc">{{ tone.desc }}</div>
              </div>
              <div class="tone-check">✓</div>
            </button>
          </div>

          <button
            class="btn-continue"
            :class="selectedTone ? 'btn-v' : 'btn-dim'"
            :disabled="!selectedTone"
            :style="{ boxShadow: selectedTone ? '0 8px 32px rgba(124,58,237,.4)' : 'none' }"
            @click="startLoading()"
          >
            Meet Your Future Self →
          </button>
        </div>

        <!-- STEP 6: LOADING -->
        <div v-show="currentStep === 5" :class="['step', { active: currentStep === 5 }]">
          <div class="loading-wrap">
            <div class="loading-orb-wrap">
              <div class="loading-orb">✨</div>
              <div class="loading-ring"></div>
            </div>
            <h2 class="loading-title">Creating your <span class="grad">Future You</span><span id="l-dots">{{ loadingDots }}</span></h2>
            <p class="loading-sub">Shaping a version of you that's 5 years ahead, ready to guide you forward.</p>
            <div class="loading-checklist">
              <div v-for="(item, i) in loadingItems" :key="i" class="check-row">
                <div class="check-dot" :class="{ done: i < completedLoadingSteps }">
                  {{ i < completedLoadingSteps ? '✓' : '' }}
                </div>
                <span class="check-txt" :class="{ done: i < completedLoadingSteps }">{{ item }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import OnboardingService from '@/services/onboardingService'

const router = useRouter()
const auth = useAuthStore()

// State
const currentStep = ref(0)
const showGoalPanel = ref(false)
const showFearPanel = ref(false)
const customTraitInput = ref('')
const roleModelInput = ref('')
const loadingDots = ref('...')
const completedLoadingSteps = ref(0)
const saving = ref(false)

const newGoal = reactive({
  title: '',
  desc: '',
  cat: '',
  tf: 'short-term',
  prio: 3,
})

const newFear = reactive({
  text: '',
  cat: '',
  int: 3,
})

const goals = ref([])
const fears = ref([])
const selectedTraits = ref(new Set())
const customTraits = ref([])
const roleModels = ref([])
const selectedTone = ref('')

// Constants
const goalCategories = ['Career', 'Money', 'Health', 'Relationships', 'Education', 'Purpose', 'Travel', 'Creativity']
const fearCategories = ['Self-doubt', 'Failure', 'Rejection', 'Commitment', 'Change', 'Loneliness', 'Financial', 'Health']
const traitOptions = ['Calm', 'Confident', 'Focused', 'Fearless', 'Resilient', 'Decisive', 'Empathetic', 'Disciplined', 'Creative', 'Ambitious', 'Patient', 'Humble']
const roleModelSuggestions = ['Kobe Bryant', 'Elon Musk', 'Steve Jobs', 'Oprah Winfrey', 'Marcus Aurelius', 'David Goggins', 'Warren Buffett', 'Nelson Mandela']
const tones = [
  { key: 'supportive', label: 'Supportive', desc: 'Gentle, encouraging, always in your corner', icon: '🤗' },
  { key: 'strict', label: 'Strict', desc: 'No excuses. High standards. Real talk.', icon: '🪖' },
  { key: 'motivational', label: 'Motivational', desc: 'Energize you before every move', icon: '⚡' },
  { key: 'friendly', label: 'Friendly', desc: 'Like talking to your best future self', icon: '✨' },
]
const loadingItems = [
  'Analyzing your goals',
  'Processing your fears',
  'Shaping your identity',
  'Learning from your heroes',
  'Setting your communication style',
  'Awakening Future You',
]
const progressLabels = ['Goals', 'Fears', 'Traits', 'Role Models', 'Tone']

// Computed
const currentStepNumber = computed(() => currentStep.value)

const isStep0 = computed(() => currentStep.value == 0)
const isStep1 = computed(() => currentStep.value == 1)
const isStep2 = computed(() => currentStep.value == 2)
const isStep3 = computed(() => currentStep.value == 3)
const isStep4 = computed(() => currentStep.value == 4)
const isStep5 = computed(() => currentStep.value == 5)

// ── Validation helpers ──
const validateGoal = () => {
  if (!newGoal.title.trim()) {
    auth.toastMessage('Goal title is required.', 'error')
    return false
  }
  if (newGoal.title.trim().length > 255) {
    auth.toastMessage('Goal title must be under 255 characters.', 'error')
    return false
  }
  return true
}

const validateFear = () => {
  if (!newFear.text.trim()) {
    auth.toastMessage('Please describe your fear.', 'error')
    return false
  }
  return true
}

const validateTraits = () => {
  if (selectedTraits.value.size === 0) {
    auth.toastMessage('Select at least one trait.', 'error')
    return false
  }
  return true
}

const validateRoleModels = () => {
  if (roleModels.value.length === 0) {
    auth.toastMessage('Add at least one role model.', 'error')
    return false
  }
  return true
}

const validateTone = () => {
  if (!selectedTone.value) {
    auth.toastMessage('Please choose a tone.', 'error')
    return false
  }
  return true
}

// ── Extract validation errors from API 422 response ──
const handleApiError = (err, fallbackMsg) => {
  if (err.response?.status === 422 && err.response?.data?.errors) {
    const firstField = Object.keys(err.response.data.errors)[0]
    auth.toastMessage(err.response.data.errors[firstField][0], 'error')
  } else {
    auth.toastMessage(err.response?.data?.message || fallbackMsg, 'error')
  }
}

const getDetail = async (type) => {
  try{
    const data = {
      type: type,
    }
    
    if(type != 'goals') data.goal_id = goals.value[0]?.id
    
    const response = await OnboardingService.getDetail(data)
    return response.data?.data
  } catch(err) {
    console.error(err)
  }
}

// Methods - Goals
const addGoal = async () => {
  if (!validateGoal()) return
  if (saving.value) return
  saving.value = true

  const goalData = {
    title: newGoal.title.trim(),
    description: newGoal.desc.trim(),
    category: newGoal.cat || goalCategories[0],
    timeframe: newGoal.tf,
    priority: String(newGoal.prio),
  }

  try {
    const response = await OnboardingService.saveGoals(goalData)
    const savedGoal = response.data?.data

    goals.value.push({
      id: savedGoal?.id || Date.now(),
      title: newGoal.title,
      desc: newGoal.desc,
      cat: newGoal.cat,
      tf: newGoal.tf,
      prio: newGoal.prio,
    })
    console.log(goals.value)

    newGoal.title = ''
    newGoal.desc = ''
    newGoal.cat = ''
    newGoal.tf = 'short'
    newGoal.prio = 3

    if (goals.value.length === 1) {
      showGoalPanel.value = false
    }

    auth.toastMessage('Goal saved!', {type:'success'})
  } catch (err) {
    console.error(err)
    handleApiError(err, 'Failed to save goal. Please try again.')
  } finally {
    saving.value = false
  }
}

const removeRole = (name) => {
  const idx = roleModels.value.findIndex(item => item.name === name)
  if (idx > -1) roleModels.value.splice(idx, 1)
}

const removeDetail = async (id, type) => {
  try {
    await OnboardingService.removeDetail({ id, type })

    auth.toastMessage('Removed successfully!', { type: 'success' })

    if (type === 'goals') {
      const idx = goals.value.findIndex(item => item.id === id)
      if (idx > -1) goals.value.splice(idx, 1)
      if (!goals.value.length) showGoalPanel.value = true
    } else if (type === 'fears') {
      const idx = fears.value.findIndex(item => item.id === id)
      if (idx > -1) fears.value.splice(idx, 1)
      if (!fears.value.length) showFearPanel.value = true
    } else if (type === 'role-models') {
      const idx = roleModels.value.findIndex(item => item.id === id)
      if (idx > -1) roleModels.value.splice(idx, 1)
    } else if (type === 'desired-traits') {
      const idx = customTraits.value.findIndex(item => item.id === id)
      if (idx > -1) {
        selectedTraits.value.delete(customTraits.value[idx].name)
        customTraits.value.splice(idx, 1)
      }
    }
  } catch (err) {
    console.error(err)
    handleApiError(err, 'Failed to remove. Please try again.')
  }
}

// Methods - Fears
const addFear = async () => {
  if (!validateFear()) return
  if (saving.value) return
  saving.value = true

  const fearData = {
    goal_id: goals.value[0]?.id,
    fear: newFear.text.trim(),
    category: newFear.cat || fearCategories[0],
    priority: String(newFear.int)  
  }

  try {
    const response = await OnboardingService.saveFears(fearData)
    const savedFear = response.data?.data

    fears.value.push({
      id: savedFear?.id || Date.now(),
      text: newFear.text,
      cat: newFear.cat,
      int: newFear.int,
    })

    newFear.text = ''
    newFear.cat = ''
    newFear.int = 3

    if (fears.value.length === 1) {
      showFearPanel.value = false
    }

    auth.toastMessage('Fear saved!', {type:'success'})
  } catch (err) {
    console.error(err)
    handleApiError(err, 'Failed to save fear. Please try again.')
  } finally {
    saving.value = false
  }
}

// const removeFear = (id) => {
//   const idx = fears.value.findIndex(f => f.id === id)
//   if (idx > -1) fears.value.splice(idx, 1)
//   if (!fears.value.length) showFearPanel.value = true
// }

const updateIntensityLabel = () => {
  // This function is called when intensity is updated
}

// Methods - Traits
const toggleTrait = (trait) => {
  if (selectedTraits.value.has(trait)) {
    selectedTraits.value.delete(trait)
  } else {
    selectedTraits.value.add(trait)
  }
}

const addCustomTrait = () => {
  const v = customTraitInput.value.trim()
  if (!v || selectedTraits.value.has(v)) return
  selectedTraits.value.add(v)
  customTraits.value.push(v)
  customTraitInput.value = ''
}

// const removeCustomTrait = (trait) => {
//   selectedTraits.value.delete(trait)
//   const idx = customTraits.value.indexOf(trait)
//   if (idx > -1) customTraits.value.splice(idx, 1)
// }

// ── Save traits via API ──
const saveTraits = async () => {
  if (!validateTraits()) return false
  if (saving.value) return false
  saving.value = true

  try {
    await OnboardingService.saveDesiredTraits({
      goal_id: goals.value[0]?.id,
      traits: Array.from(selectedTraits.value),
    })
    auth.toastMessage('Traits saved!', {type:'success'})
    return true
  } catch (err) {
    console.error(err)
    handleApiError(err, 'Failed to save traits. Please try again.')
    return false
  } finally {
    saving.value = false
  }
}

// Methods - Role Models
const addRoleModel = () => {
  const v = roleModelInput.value.trim()
  if (!v || roleModels.value.some(r => r.name === v)) {
    roleModelInput.value = ''
    return
  }
  roleModels.value.push({ id: null, name: v })
  roleModelInput.value = ''
}

const addRoleModelFromSugg = (sugg) => {
  if (!roleModels.value.some(r => r.name === sugg)) {
    roleModels.value.push({ id: null, name: sugg })
  }
}

// const removeRoleModel = (role) => {
//   const idx = roleModels.value.indexOf(role)
//   if (idx > -1) roleModels.value.splice(idx, 1)
// }

// ── Save role models via API ──
const saveRoleModels = async () => {
  if (!validateRoleModels()) return false
  if (saving.value) return false
  saving.value = true

  try {
    await OnboardingService.saveRoleModels({
      goal_id: goals.value[0]?.id,
      names: roleModels.value.map(r => r.name),
    })

    // Re-fetch to get real database IDs for newly added items
    const refreshed = await getDetail('role-models')
    if (refreshed && refreshed.length) {
      roleModels.value = refreshed.map(r => ({ id: r.id, name: r.names }))
    }

    auth.toastMessage('Role models saved!', {type:'success'})
    return true
  } catch (err) {
    console.error(err)
    handleApiError(err, 'Failed to save role models. Please try again.')
    return false
  } finally {
    saving.value = false
  }
}

// Methods - Tone & Navigation
const selectTone = (toneKey) => {
  selectedTone.value = toneKey
}

const updateOnboarded = async () => {
  try {
    await OnboardingService.updateOnboarded()
    await router.push({
      name: auth.user?.is_onboarded ? 'Dashboard' : 'Onboarding',
    })
    auth.toastMessage('Onboarded successfully!', {type:'success'})
  } catch (err) {
    console.error(err)
    handleApiError(err, 'Failed to onboard. Please try again.')
    return false
  }
}

const goNext = async () => {
  if (saving.value) return

  // Step 0 → 1 : Goals (already saved individually, just validate at least one exists)
  if (currentStep.value === 0) {
    if (!goals.value.length) {
      auth.toastMessage('Add at least one goal to continue.', 'error')
      return
    }
  }

  // Step 1 → 2 : Fears (already saved individually, just validate at least one exists)
  if (currentStep.value === 1) {
    if (!fears.value.length) {
      auth.toastMessage('Add at least one fear to continue.', 'error')
      return
    }
  }

  // Step 2 → 3 : Traits (save in bulk on Continue)
  if (currentStep.value === 2) {
    const ok = await saveTraits()
    if (!ok) return
  }

  // Step 3 → 4 : Role Models (save in bulk on Continue)
  if (currentStep.value === 3) {
    const hasNewRoleModels = roleModels.value.some(r => r.id === null)
    if (hasNewRoleModels) {
      const ok = await saveRoleModels()
      if (!ok) return
    }
    // If no role models or no new ones, just move forward
  }

  if (currentStep.value < 5) {
    currentStep.value++
  }
}

const goBack = () => {
  if (currentStep.value > 0) {
    currentStep.value--
  }
}

const handleLogout = async () => {
  try {
    const response = await auth.logout()
    await router.push({ name: 'Login' })

    auth.toastMessage(response?.message || 'You have been logged out.', { type: 'success' })
  } catch (err) {
    console.error(err)
    auth.toastMessage('Unable to log out right now.', { type: 'error' })
  }
}

const startLoading = async () => {
  if (!validateTone()) return
  if (saving.value) return
  saving.value = true

  try {
    await OnboardingService.saveTone({
      goal_id: goals.value[0]?.id,
      tone: selectedTone.value,
    })
    auth.toastMessage('Tone saved!', { type: 'success' })
  } catch (err) {
    console.error(err)
    handleApiError(err, 'Failed to save tone. Please try again.')
    saving.value = false
    return
  } finally {
    saving.value = false
  }

  currentStep.value = 5
  let tick = 0
  const dotsCyc = ['', '.', '..', '...']
  const interval = setInterval(() => {
    tick++
    loadingDots.value = dotsCyc[tick % 4]
    completedLoadingSteps.value = Math.min(tick, 6)
  }, 500)
  setTimeout(() => {
    clearInterval(interval)
    updateOnboarded()
  }, 5000)
}

// ── Load existing data on mount ──
const loadExistingData = async () => {
  try {
    // 1. Fetch goals
    const goalsData = await getDetail('goals')
    
    if (goalsData) {
      console.log(goalsData);
      
      goals.value.push({
        id: goalsData.id,
        title: goalsData.title,
        desc: goalsData.description || '',
        cat: goalsData.category || '',
        tf: goalsData.timeframe || 'short-term',
        prio: Number(goalsData.priority) || 3,
      })
      showGoalPanel.value = false
    }else{
      showGoalPanel.value = true
    }

      // 2. Fetch fears (needs goal_id)
      const fearsData = await getDetail('fears')
      
      if (fearsData) {
        fears.value.push({
          id: fearsData.id,
          text: fearsData.fear,
          cat: fearsData.category || '',
          int: Number(fearsData.priority) || 3,
        })
        showFearPanel.value = false
      } else {
        showFearPanel.value = true
      }

      // 3. Fetch desired traits
      const traitsData = await getDetail('desired-traits')
      if (traitsData && traitsData.length) {
        traitsData.forEach(t => {
          const traitName = t.trait
          selectedTraits.value.add(traitName)
          // If it's not a preset trait, add it as custom
          if (!traitOptions.includes(traitName)) {
            customTraits.value.push(traitName)
          }
        })
      }else{
        showTraitPanel.value = true
      }

      // 4. Fetch role models
      const roleModelsData = await getDetail('role-models')
      if (roleModelsData && roleModelsData.length) {
        roleModels.value = roleModelsData.map(r => ({ id: r.id, name: r.names }))
      }else{
        showRolePanel.value = true
      }

      // 5. Fetch communication tone
      const toneData = await getDetail('tone')
      if (toneData) {
        selectedTone.value = toneData.tone
      }
  } catch (err) {
    console.error('Failed to load existing onboarding data:', err)
  }
}

// Initialize
onMounted(() => {
  loadExistingData()
})
</script>

<style scoped>
@import url('@/assets/styles/onboarding.css');

.onboarding-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}
</style>
