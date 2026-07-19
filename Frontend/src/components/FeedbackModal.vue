<script setup>
import { ref, reactive, computed, watch } from 'vue'
import feedbackService from '@/services/feedbackService'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  initialData: {
    type: Object,
    default: () => null
  }
})

const emit = defineEmits(['close', 'submitted', 'skipped'])

const currentStep = ref(1)
const totalSteps = 4
const isSubmitting = ref(false)
const isSubmitted = ref(false)

const form = reactive({
  helpful_rating: 0,
  is_personal: '',
  understood_level: '',
  would_use_again: '',
  use_frequency: '',
  most_valuable: '',
  confused_or_disappointed: '',
  feature_to_come_back: '',
  monthly_price_willingness: '',
  subscription_convincer: '',
  nps_score: null,
  one_thing_to_change: ''
})

const isEditing = computed(() => {
  return !!(props.initialData && !props.initialData.is_skipped && props.initialData.id)
})

watch(() => props.show, (newVal) => {
  if (newVal) {
    currentStep.value = 1
    isSubmitted.value = false
    if (props.initialData) {
      form.helpful_rating = props.initialData.helpful_rating || 0
      form.is_personal = props.initialData.is_personal || ''
      form.understood_level = props.initialData.understood_level || ''
      form.would_use_again = props.initialData.would_use_again || ''
      form.use_frequency = props.initialData.use_frequency || ''
      form.most_valuable = props.initialData.most_valuable || ''
      form.confused_or_disappointed = props.initialData.confused_or_disappointed || ''
      form.feature_to_come_back = props.initialData.feature_to_come_back || ''
      form.monthly_price_willingness = props.initialData.monthly_price_willingness || ''
      form.subscription_convincer = props.initialData.subscription_convincer || ''
      form.nps_score = props.initialData.nps_score !== undefined && props.initialData.nps_score !== null
        ? props.initialData.nps_score
        : null
      form.one_thing_to_change = props.initialData.one_thing_to_change || ''
    }
  }
})

// Options
const personalOptions = ['Yes', 'Somewhat', 'No']
const understoodOptions = ['Always', 'Most of the time', 'Sometimes', 'Rarely']
const wouldUseOptions = ['Definitely', 'Probably', 'Maybe', 'No']
const frequencyOptions = ['Daily', 'A few times a week', 'Weekly', 'Only when needed']
const pricingOptions = ['₹99', '₹199', '₹299', '₹499', 'More than ₹499', "I wouldn't pay"]
const npsScores = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]

const progressPercent = computed(() => {
  return (currentStep.value / totalSteps) * 100
})

function setRating(stars) {
  form.helpful_rating = stars
}

function selectOption(field, val) {
  form[field] = val
}

function nextStep() {
  if (currentStep.value < totalSteps) {
    currentStep.value++
  }
}

function prevStep() {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

async function handleSkip() {
  try {
    await feedbackService.skipFeedback()
  } catch (err) {
    console.error('Failed to register skip', err)
  } finally {
    emit('skipped')
    emit('close')
    resetForm()
  }
}

async function handleSubmit() {
  isSubmitting.value = true
  try {
    const res = await feedbackService.submitFeedback(form)
    const savedData = res.data.data ? res.data.data.feedback : null
    isSubmitted.value = true
    setTimeout(() => {
      emit('submitted', savedData)
      emit('close')
      resetForm()
    }, 1500)
  } catch (err) {
    console.error('Failed to submit feedback', err)
  } finally {
    isSubmitting.value = false
  }
}

function resetForm() {
  currentStep.value = 1
  isSubmitted.value = false
  form.helpful_rating = 0
  form.is_personal = ''
  form.understood_level = ''
  form.would_use_again = ''
  form.use_frequency = ''
  form.most_valuable = ''
  form.confused_or_disappointed = ''
  form.feature_to_come_back = ''
  form.monthly_price_willingness = ''
  form.subscription_convincer = ''
  form.nps_score = null
  form.one_thing_to_change = ''
}
</script>

<template>
  <Teleport to="body">
    <Transition name="feedback-modal-fade">
      <div v-if="show" class="feedback-modal-overlay" @click.self="handleSkip">
        <div class="feedback-modal-container">
          
          <!-- Close / Skip Button top right -->
          <button class="feedback-close-btn" @click="handleSkip" title="Skip feedback" id="btn-feedback-close">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>

          <!-- Submitted Success View -->
          <div v-if="isSubmitted" class="feedback-success-state">
            <div class="feedback-success-icon">✨</div>
            <h3 class="feedback-success-title">Thank You!</h3>
            <p class="feedback-success-desc">
              Your feedback directly shapes <strong>FutureYou</strong> ❤️
            </p>
          </div>

          <!-- Form View -->
          <div v-else class="feedback-form-content">
            <!-- Header -->
            <div class="feedback-modal-header">
              <div class="feedback-badge">Beta Feedback</div>
              <h2 class="feedback-modal-title">Help Shape FutureYou</h2>
              <p class="feedback-modal-subtitle">Daily limit reached — take a moment to share your experience (Optional)</p>
            </div>

            <!-- Progress Bar -->
            <div class="feedback-progress-wrapper">
              <div class="feedback-progress-info">
                <span>Step {{ currentStep }} of {{ totalSteps }}</span>
                <span>{{ Math.round(progressPercent) }}%</span>
              </div>
              <div class="feedback-progress-track">
                <div class="feedback-progress-fill" :style="{ width: progressPercent + '%' }"></div>
              </div>
            </div>

            <!-- Steps Body -->
            <div class="feedback-step-body">
              
              <!-- STEP 1: Overall Experience -->
              <div v-if="currentStep === 1" class="feedback-step-content">
                <div class="feedback-section-title">1. Overall Experience</div>

                <!-- Q1: Star Rating -->
                <div class="feedback-group">
                  <label class="feedback-label">How helpful was your conversation?</label>
                  <div class="star-rating-row">
                    <button
                      v-for="star in 5"
                      :key="star"
                      type="button"
                      class="star-btn"
                      :class="{ active: star <= form.helpful_rating }"
                      @click="setRating(star)"
                      :id="'star-btn-' + star"
                    >
                      ★
                    </button>
                  </div>
                  <div class="star-hint" v-if="form.helpful_rating > 0">
                    {{ form.helpful_rating === 1 ? '1 - Not helpful' : form.helpful_rating === 5 ? '5 - Extremely helpful' : `${form.helpful_rating} / 5` }}
                  </div>
                </div>

                <!-- Q2: Personal feel -->
                <div class="feedback-group">
                  <label class="feedback-label">Did FutureYou feel personal?</label>
                  <div class="chip-options">
                    <button
                      v-for="opt in personalOptions"
                      :key="opt"
                      type="button"
                      class="chip-btn"
                      :class="{ selected: form.is_personal === opt }"
                      @click="selectOption('is_personal', opt)"
                      :id="'chip-personal-' + opt.toLowerCase()"
                    >
                      {{ opt }}
                    </button>
                  </div>
                </div>

                <!-- Q3: Advice understanding -->
                <div class="feedback-group">
                  <label class="feedback-label">Did the advice feel like it understood you?</label>
                  <div class="chip-options">
                    <button
                      v-for="opt in understoodOptions"
                      :key="opt"
                      type="button"
                      class="chip-btn"
                      :class="{ selected: form.understood_level === opt }"
                      @click="selectOption('understood_level', opt)"
                    >
                      {{ opt }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- STEP 2: Product Validation -->
              <div v-else-if="currentStep === 2" class="feedback-step-content">
                <div class="feedback-section-title">2. Product & Usage</div>

                <!-- Q4: Would use again -->
                <div class="feedback-group">
                  <label class="feedback-label">Would you use FutureYou again?</label>
                  <div class="chip-options">
                    <button
                      v-for="opt in wouldUseOptions"
                      :key="opt"
                      type="button"
                      class="chip-btn"
                      :class="{ selected: form.would_use_again === opt }"
                      @click="selectOption('would_use_again', opt)"
                    >
                      {{ opt }}
                    </button>
                  </div>
                </div>

                <!-- Q5: Frequency -->
                <div class="feedback-group">
                  <label class="feedback-label">How often would you use it?</label>
                  <div class="chip-options">
                    <button
                      v-for="opt in frequencyOptions"
                      :key="opt"
                      type="button"
                      class="chip-btn"
                      :class="{ selected: form.use_frequency === opt }"
                      @click="selectOption('use_frequency', opt)"
                    >
                      {{ opt }}
                    </button>
                  </div>
                </div>

                <!-- Q6: Most valuable part -->
                <div class="feedback-group">
                  <label class="feedback-label">What was the most valuable part of the conversation?</label>
                  <textarea
                    v-model="form.most_valuable"
                    class="feedback-textarea"
                    rows="2"
                    placeholder="Share what resonated with you..."
                  ></textarea>
                </div>
              </div>

              <!-- STEP 3: Pricing & Net Promoter Score -->
              <div v-else-if="currentStep === 3" class="feedback-step-content">
                <div class="feedback-section-title">3. Value & Recommendation</div>

                <!-- Q7: Confused or disappointed -->
                <div class="feedback-group">
                  <label class="feedback-label">What confused or disappointed you?</label>
                  <textarea
                    v-model="form.confused_or_disappointed"
                    class="feedback-textarea"
                    rows="2"
                    placeholder="Tell us what could be improved..."
                  ></textarea>
                </div>

                <!-- Q9: Pricing -->
                <div class="feedback-group">
                  <label class="feedback-label">If FutureYou had long-term memory & new features, how much would you pay per month?</label>
                  <div class="chip-options wrap">
                    <button
                      v-for="opt in pricingOptions"
                      :key="opt"
                      type="button"
                      class="chip-btn"
                      :class="{ selected: form.monthly_price_willingness === opt }"
                      @click="selectOption('monthly_price_willingness', opt)"
                    >
                      {{ opt }}
                    </button>
                  </div>
                </div>

                <!-- Q11: NPS Scale -->
                <div class="feedback-group">
                  <label class="feedback-label">How likely are you to recommend FutureYou to a friend? (0 = Not at all, 10 = Extremely likely)</label>
                  <div class="nps-grid">
                    <button
                      v-for="num in npsScores"
                      :key="num"
                      type="button"
                      class="nps-btn"
                      :class="{ selected: form.nps_score === num }"
                      @click="form.nps_score = num"
                      :id="'nps-btn-' + num"
                    >
                      {{ num }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- STEP 4: Final Thoughts -->
              <div v-else-if="currentStep === 4" class="feedback-step-content">
                <div class="feedback-section-title">4. Final Thoughts</div>

                <!-- Q8: Feature request -->
                <div class="feedback-group">
                  <label class="feedback-label">What feature would make you come back every day?</label>
                  <textarea
                    v-model="form.feature_to_come_back"
                    class="feedback-textarea"
                    rows="2"
                    placeholder="Your dream feature..."
                  ></textarea>
                </div>

                <!-- Q10: Subscription convince -->
                <div class="feedback-group">
                  <label class="feedback-label">What would convince you to subscribe?</label>
                  <textarea
                    v-model="form.subscription_convincer"
                    class="feedback-textarea"
                    rows="2"
                    placeholder="Features, insights, memory..."
                  ></textarea>
                </div>

                <!-- Q12: One thing to change -->
                <div class="feedback-group">
                  <label class="feedback-label">If you could change one thing about FutureYou, what would it be?</label>
                  <textarea
                    v-model="form.one_thing_to_change"
                    class="feedback-textarea"
                    rows="2"
                    placeholder="Any final thought..."
                  ></textarea>
                </div>
              </div>

            </div>

            <!-- Footer Actions -->
            <div class="feedback-modal-footer">
              <button
                type="button"
                class="btn-feedback-skip"
                @click="handleSkip"
                id="btn-feedback-skip-bottom"
              >
                Skip for now
              </button>

              <div class="footer-right-btns">
                <button
                  v-if="currentStep > 1"
                  type="button"
                  class="btn-feedback-secondary"
                  @click="prevStep"
                  id="btn-feedback-prev"
                >
                  Back
                </button>

                <button
                  v-if="currentStep < totalSteps"
                  type="button"
                  class="btn-feedback-primary"
                  @click="nextStep"
                  id="btn-feedback-next"
                >
                  Next
                </button>

                <button
                  v-else
                  type="button"
                  class="btn-feedback-submit"
                  :disabled="isSubmitting"
                  @click="handleSubmit"
                  id="btn-feedback-submit"
                >
                  <span v-if="isSubmitting">Saving...</span>
                  <span v-else-if="isEditing">Update Feedback ❤️</span>
                  <span v-else>Submit Feedback ❤️</span>
                </button>
              </div>
            </div>

          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
/* Scoped overrides if needed; main styles in chat.css */
</style>
