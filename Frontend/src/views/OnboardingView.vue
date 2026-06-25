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
      <div style="width: 120px"></div>
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
          <p class="step-sub">What are you working towards? Add as many as you like — big or small.</p>

          <div class="saved-list">
            <div v-for="goal in goals" :key="goal.id" class="saved-goal">
              <div class="saved-body">
                <div class="saved-row">
                  <span class="saved-ttl">{{ goal.title }}</span>
                  <span class="badge" :class="goal.tf === 'short' ? 'badge-short' : 'badge-long'">
                    {{ goal.tf === 'short' ? 'short-term' : 'long-term' }}
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
              <button class="btn-rm" @click="removeGoal(goal.id)">×</button>
            </div>
          </div>

          <!-- Panel -->
          <div v-show="showGoalPanel" class="panel panel-v">
            <div class="panel-hd">New Goal</div>
            <div class="field">
              <!-- <div> -->
                <label class="lbl">Title <span>*</span></label>
                <input v-model="newGoal.title" type="text" placeholder="e.g. Build my own company" />
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
                  <button class="tf-btn" :class="{ short: newGoal.tf === 'short' }" @click="newGoal.tf = 'short'">
                    ⚡ Short-term
                  </button>
                  <button class="tf-btn" :class="{ long: newGoal.tf === 'long' }" @click="newGoal.tf = 'long'">
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
              <button class="btn-rm" @click="removeFear(fear.id)">×</button>
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
                <button @click="removeCustomTrait(trait)">×</button>
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
                v-for="sugg in roleModelSuggestions.filter(s => !roleModels.includes(s))"
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
              <div v-for="(role, i) in roleModels" :key="i" class="role-row">
                <div class="role-num">{{ i + 1 }}</div>
                <span class="role-name">{{ role }}</span>
                <button class="btn-rm" @click="removeRoleModel(role)">×</button>
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
import { ref, reactive, computed } from 'vue'

// State
const currentStep = ref(0)
const showGoalPanel = ref(false)
const showFearPanel = ref(false)
const customTraitInput = ref('')
const roleModelInput = ref('')
const loadingDots = ref('...')
const completedLoadingSteps = ref(0)

const newGoal = reactive({
  title: '',
  desc: '',
  cat: '',
  tf: 'short',
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

// Methods - Goals
const addGoal = () => {
  if (!newGoal.title.trim()) return
  goals.value.push({
    id: Date.now(),
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

  
}

const removeGoal = (id) => {
  const idx = goals.value.findIndex(g => g.id === id)
  if (idx > -1) goals.value.splice(idx, 1)
  if (!goals.value.length) showGoalPanel.value = true
}

// Methods - Fears
const addFear = () => {
  if (!newFear.text.trim()) return
  fears.value.push({
    id: Date.now(),
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
}

const removeFear = (id) => {
  const idx = fears.value.findIndex(f => f.id === id)
  if (idx > -1) fears.value.splice(idx, 1)
  if (!fears.value.length) showFearPanel.value = true
}

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

const removeCustomTrait = (trait) => {
  selectedTraits.value.delete(trait)
  const idx = customTraits.value.indexOf(trait)
  if (idx > -1) customTraits.value.splice(idx, 1)
}

// Methods - Role Models
const addRoleModel = () => {
  const v = roleModelInput.value.trim()
  if (!v || roleModels.value.includes(v)) {
    roleModelInput.value = ''
    return
  }
  roleModels.value.push(v)
  roleModelInput.value = ''
}

const addRoleModelFromSugg = (sugg) => {
  if (!roleModels.value.includes(sugg)) {
    roleModels.value.push(sugg)
  }
}

const removeRoleModel = (role) => {
  const idx = roleModels.value.indexOf(role)
  if (idx > -1) roleModels.value.splice(idx, 1)
}

// Methods - Tone & Navigation
const selectTone = (toneKey) => {
  selectedTone.value = toneKey
}

const goNext = () => {
  if (currentStep.value < 5) {
    currentStep.value++
  }
}

const startLoading = () => {
  currentStep.value = 5
  let tick = 0
  const dotsCyc = ['', '.', '..', '...']
  const interval = setInterval(() => {
    tick++
    loadingDots.value = dotsCyc[tick % 4]
    completedLoadingSteps.value = Math.min(tick, 6)
  }, 500)
  setTimeout(() => clearInterval(interval), 5000)
}

// Initialize
showGoalPanel.value = true
showFearPanel.value = true
</script>

<style scoped>
@import url('@/assets/styles/onboarding.css');

.onboarding-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}
</style>
