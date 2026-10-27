<script setup>
import { ref, reactive, watch, onMounted, onBeforeUnmount } from 'vue'
import { useAuthStore } from '@/stores/auth'
import contactService from '@/services/contactService'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  initialCategory: {
    type: String,
    default: 'General Inquiry'
  }
})

const emit = defineEmits(['close', 'submitted'])

const auth = useAuthStore()

const isSubmitting = ref(false)
const isSubmitted = ref(false)
const errorMessage = ref('')

const categories = [
  { id: 'General Inquiry', label: '💬 General Query' },
  { id: 'Feature Request', label: '💡 Feature Idea' },
  { id: 'Bug Report', label: '🐞 Bug Report' },
  { id: 'Account & Billing', label: '💳 Account & Billing' },
  { id: 'Other', label: '✨ Other' }
]

const form = reactive({
  name: '',
  email: '',
  category: 'General Inquiry',
  subject: '',
  message: ''
})

const errors = reactive({
  name: '',
  email: '',
  subject: '',
  message: ''
})

function populateUserData() {
  if (auth.user) {
    if (!form.name && auth.user.name) form.name = auth.user.name
    if (!form.email && auth.user.email) form.email = auth.user.email
  }
}

watch(() => props.show, (newVal) => {
  if (newVal) {
    document.body.style.overflow = 'hidden'
    errorMessage.value = ''
    isSubmitted.value = false
    populateUserData()
    if (props.initialCategory) {
      form.category = props.initialCategory
    }
  } else {
    document.body.style.overflow = ''
  }
})

function selectCategory(cat) {
  form.category = cat
}

function validate() {
  let isValid = true
  errors.name = ''
  errors.email = ''
  errors.subject = ''
  errors.message = ''
  errorMessage.value = ''

  if (!form.name.trim()) {
    errors.name = 'Please enter your name.'
    isValid = false
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!form.email.trim()) {
    errors.email = 'Please enter your email address.'
    isValid = false
  } else if (!emailRegex.test(form.email.trim())) {
    errors.email = 'Please enter a valid email address.'
    isValid = false
  }

  if (!form.subject.trim()) {
    errors.subject = 'Please enter a query subject.'
    isValid = false
  }

  if (!form.message.trim()) {
    errors.message = 'Please provide details for your query.'
    isValid = false
  } else if (form.message.trim().length < 5) {
    errors.message = 'Message must be at least 5 characters long.'
    isValid = false
  }

  return isValid
}

async function handleSubmit() {
  if (!validate()) return

  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const payload = {
      name: form.name.trim(),
      email: form.email.trim(),
      category: form.category,
      subject: form.subject.trim(),
      message: form.message.trim()
    }

    const res = await contactService.submitContact(payload)
    isSubmitted.value = true
    emit('submitted', res.data)
    if (auth.toastMessage) {
      auth.toastMessage('Your message has been sent successfully! ✨', 'success')
    }
  } catch (err) {
    console.error('Failed to submit contact query', err)
    errorMessage.value = err.response?.data?.message || 'Something went wrong while sending your message. Please try again.'
  } finally {
    isSubmitting.value = false
  }
}

function handleClose() {
  emit('close')
  // Reset non-user fields after transition
  setTimeout(() => {
    isSubmitted.value = false
    form.subject = ''
    form.message = ''
    errorMessage.value = ''
    errors.name = ''
    errors.email = ''
    errors.subject = ''
    errors.message = ''
  }, 300)
}

function handleKeydown(e) {
  if (e.key === 'Escape' && props.show) {
    handleClose()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <Transition name="contact-modal-fade">
      <div v-if="show" class="contact-modal-overlay" @click.self="handleClose" role="dialog" aria-modal="true">
        <div class="contact-modal-container">
          
          <!-- Close Button -->
          <button
            class="contact-close-btn"
            @click="handleClose"
            title="Close modal"
            id="btn-contact-close"
            type="button"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>

          <!-- Success State -->
          <div v-if="isSubmitted" class="contact-success-state">
            <div class="contact-success-glow"></div>
            <div class="contact-success-badge">✨ Message Sent!</div>
            <h3 class="contact-success-title">We've Received Your Query</h3>
            <p class="contact-success-desc">
              Thank you for reaching out, <strong>{{ form.name }}</strong>! Our team typically reviews queries and replies back to <strong>{{ form.email }}</strong> within 24 hours.
            </p>
            <div class="contact-success-actions">
              <button
                type="button"
                class="btn-contact-primary"
                @click="handleClose"
                id="btn-contact-success-close"
              >
                Done
              </button>
              <button
                type="button"
                class="btn-contact-secondary"
                @click="isSubmitted = false; form.subject = ''; form.message = ''"
              >
                Send Another Query
              </button>
            </div>
          </div>

          <!-- Form View -->
          <div v-else class="contact-form-content">
            <!-- Header -->
            <div class="contact-modal-header">
              <div class="contact-badge">
                <span class="contact-badge-dot"></span>
                Support & Queries
              </div>
              <h2 class="contact-modal-title">Have a Query or Need Help?</h2>
              <p class="contact-modal-subtitle">
                Drop us a line with any questions, bug reports, or feature ideas. We're here to help you get the most out of Future Self.
              </p>
            </div>

            <!-- Error Banner -->
            <div v-if="errorMessage" class="contact-error-banner">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              <span>{{ errorMessage }}</span>
            </div>

            <form @submit.prevent="handleSubmit" class="contact-form-body">
              <!-- Category Chips -->
              <div class="contact-group">
                <label class="contact-label">What is your query about?</label>
                <div class="contact-category-chips">
                  <button
                    v-for="cat in categories"
                    :key="cat.id"
                    type="button"
                    class="category-chip"
                    :class="{ active: form.category === cat.id }"
                    @click="selectCategory(cat.id)"
                  >
                    {{ cat.label }}
                  </button>
                </div>
              </div>

              <!-- Two column inputs: Name & Email -->
              <div class="contact-grid">
                <div class="contact-group">
                  <label class="contact-label" for="contact-name">
                    Your Name <span class="required">*</span>
                  </label>
                  <input
                    id="contact-name"
                    v-model="form.name"
                    type="text"
                    class="contact-input"
                    :class="{ 'has-error': errors.name }"
                    placeholder="e.g. Alex Morgan"
                  />
                  <span v-if="errors.name" class="field-error">{{ errors.name }}</span>
                </div>

                <div class="contact-group">
                  <label class="contact-label" for="contact-email">
                    Your Email <span class="required">*</span>
                  </label>
                  <input
                    id="contact-email"
                    v-model="form.email"
                    type="email"
                    class="contact-input"
                    :class="{ 'has-error': errors.email }"
                    placeholder="you@example.com"
                  />
                  <span v-if="errors.email" class="field-error">{{ errors.email }}</span>
                </div>
              </div>

              <!-- Subject -->
              <div class="contact-group">
                <label class="contact-label" for="contact-subject">
                  Subject / Summary <span class="required">*</span>
                </label>
                <input
                  id="contact-subject"
                  v-model="form.subject"
                  type="text"
                  class="contact-input"
                  :class="{ 'has-error': errors.subject }"
                  placeholder="Brief summary of your question or issue..."
                />
                <span v-if="errors.subject" class="field-error">{{ errors.subject }}</span>
              </div>

              <!-- Message -->
              <div class="contact-group">
                <div class="label-row">
                  <label class="contact-label" for="contact-message">
                    Query Details <span class="required">*</span>
                  </label>
                  <span class="char-count" :class="{ 'char-warning': form.message.length > 2800 }">
                    {{ form.message.length }} / 3000
                  </span>
                </div>
                <textarea
                  id="contact-message"
                  v-model="form.message"
                  rows="4"
                  maxlength="3000"
                  class="contact-textarea"
                  :class="{ 'has-error': errors.message }"
                  placeholder="Tell us everything you need help with. Include any specifics or steps..."
                ></textarea>
                <span v-if="errors.message" class="field-error">{{ errors.message }}</span>
              </div>

              <!-- Actions -->
              <div class="contact-actions">
                <button
                  type="button"
                  class="btn-contact-cancel"
                  @click="handleClose"
                  id="btn-contact-cancel"
                >
                  Cancel
                </button>

                <button
                  type="submit"
                  class="btn-contact-primary"
                  :disabled="isSubmitting"
                  id="btn-contact-submit"
                >
                  <span v-if="isSubmitting" class="spinner-inline"></span>
                  <span v-if="isSubmitting">Sending Query...</span>
                  <span v-else>Send Message 🚀</span>
                </button>
              </div>
            </form>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
/* ── Overlay ─────────────────────────────────────── */
.contact-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(8, 6, 18, 0.85);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10001;
  padding: 20px;
}

/* ── Container ───────────────────────────────────── */
.contact-modal-container {
  position: relative;
  width: 100%;
  max-width: 620px;
  max-height: 90vh;
  background: linear-gradient(145deg, rgba(26, 20, 50, 0.98), rgba(15, 12, 32, 0.98));
  border: 1px solid rgba(167, 139, 250, 0.22);
  border-radius: 24px;
  padding: 32px 32px 28px;
  box-shadow: 0 24px 72px rgba(0, 0, 0, 0.65), 0 0 48px rgba(124, 58, 237, 0.22);
  display: flex;
  flex-direction: column;
  overflow-y: auto;
  color: #fff;
  scrollbar-width: thin;
  scrollbar-color: rgba(167, 139, 250, 0.3) transparent;
}

.contact-modal-container::-webkit-scrollbar {
  width: 6px;
}
.contact-modal-container::-webkit-scrollbar-thumb {
  background: rgba(167, 139, 250, 0.3);
  border-radius: 999px;
}

/* ── Close Button ────────────────────────────────── */
.contact-close-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  width: 36px;
  height: 36px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  z-index: 2;
}

.contact-close-btn:hover {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(239, 68, 68, 0.4);
  color: #f87171;
  transform: rotate(90deg);
}

.contact-close-btn svg {
  width: 18px;
  height: 18px;
}

/* ── Header ──────────────────────────────────────── */
.contact-modal-header {
  margin-bottom: 22px;
}

.contact-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  background: rgba(139, 92, 246, 0.16);
  border: 1px solid rgba(139, 92, 246, 0.3);
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #c4b5fd;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 12px;
}

.contact-badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #a78bfa;
  box-shadow: 0 0 8px #a78bfa;
}

.contact-modal-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #f8fafc;
  margin: 0 0 8px;
  line-height: 1.25;
}

.contact-modal-subtitle {
  font-size: 0.88rem;
  color: rgba(203, 213, 225, 0.78);
  line-height: 1.5;
  margin: 0;
}

/* ── Error Banner ────────────────────────────────── */
.contact-error-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.35);
  border-radius: 12px;
  color: #fca5a5;
  font-size: 0.85rem;
  margin-bottom: 18px;
}

/* ── Form Body ───────────────────────────────────── */
.contact-form-body {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.contact-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.contact-label {
  font-size: 0.82rem;
  font-weight: 600;
  color: rgba(226, 232, 240, 0.9);
}

.required {
  color: #f87171;
}

.char-count {
  font-size: 0.75rem;
  color: rgba(148, 163, 184, 0.7);
}

.char-warning {
  color: #fbbf24;
}

/* ── Category Chips ──────────────────────────────── */
.contact-category-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 2px;
}

.category-chip {
  padding: 7px 14px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(226, 232, 240, 0.8);
  font-size: 0.82rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  user-select: none;
}

.category-chip:hover {
  background: rgba(139, 92, 246, 0.12);
  border-color: rgba(139, 92, 246, 0.35);
  color: #e2e8f0;
}

.category-chip.active {
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.35), rgba(79, 70, 229, 0.3));
  border-color: #8b5cf6;
  color: #fff;
  box-shadow: 0 0 12px rgba(139, 92, 246, 0.3);
}

/* ── Inputs & Textarea ───────────────────────────── */
.contact-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.contact-input,
.contact-textarea {
  width: 100%;
  box-sizing: border-box;
  background: rgba(15, 12, 26, 0.65);
  border: 1px solid rgba(148, 163, 184, 0.18);
  border-radius: 12px;
  padding: 10px 14px;
  color: #f8fafc;
  font-size: 0.88rem;
  font-family: inherit;
  outline: none;
  transition: all 0.2s ease;
}

.contact-input:focus,
.contact-textarea:focus {
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
  background: rgba(15, 12, 26, 0.9);
}

.contact-input.has-error,
.contact-textarea.has-error {
  border-color: #f87171;
  background: rgba(239, 68, 68, 0.05);
}

.field-error {
  font-size: 0.75rem;
  color: #f87171;
  margin-top: 2px;
}

.contact-textarea {
  resize: vertical;
  min-height: 85px;
  line-height: 1.5;
}

/* ── Actions ─────────────────────────────────────── */
.contact-actions {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 12px;
  margin-top: 8px;
  padding-top: 14px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.btn-contact-cancel {
  padding: 10px 18px;
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  color: rgba(226, 232, 240, 0.75);
  font-size: 0.88rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-contact-cancel:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #fff;
}

.btn-contact-primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 24px;
  background: linear-gradient(135deg, #7c3aed, #6366f1);
  border: none;
  border-radius: 12px;
  color: #fff;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(124, 58, 237, 0.35);
  transition: all 0.2s ease;
}

.btn-contact-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #8b5cf6, #4f46e5);
  box-shadow: 0 6px 22px rgba(124, 58, 237, 0.45);
  transform: translateY(-1px);
}

.btn-contact-primary:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none;
}

.btn-contact-secondary {
  padding: 10px 20px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 12px;
  color: #e2e8f0;
  font-size: 0.88rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-contact-secondary:hover {
  background: rgba(255, 255, 255, 0.14);
  color: #fff;
}

.spinner-inline {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ── Success State ───────────────────────────────── */
.contact-success-state {
  text-align: center;
  padding: 30px 10px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.contact-success-badge {
  display: inline-block;
  padding: 6px 16px;
  background: rgba(34, 197, 94, 0.18);
  border: 1px solid rgba(34, 197, 94, 0.4);
  color: #4ade80;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
  margin-bottom: 16px;
}

.contact-success-title {
  font-size: 1.65rem;
  font-weight: 700;
  color: #f8fafc;
  margin: 0 0 12px;
}

.contact-success-desc {
  font-size: 0.95rem;
  color: rgba(203, 213, 225, 0.85);
  line-height: 1.6;
  max-width: 460px;
  margin: 0 0 28px;
}

.contact-success-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
}

/* ── Modal Animations ────────────────────────────── */
.contact-modal-fade-enter-active,
.contact-modal-fade-leave-active {
  transition: opacity 0.25s ease;
}

.contact-modal-fade-enter-from,
.contact-modal-fade-leave-to {
  opacity: 0;
}

.contact-modal-fade-enter-active .contact-modal-container {
  animation: modalScaleIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.contact-modal-fade-leave-active .contact-modal-container {
  animation: modalScaleOut 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalScaleIn {
  from {
    opacity: 0;
    transform: scale(0.94) translateY(12px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

@keyframes modalScaleOut {
  from {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
  to {
    opacity: 0;
    transform: scale(0.94) translateY(12px);
  }
}

/* ── Responsive ──────────────────────────────────── */
@media (max-width: 640px) {
  .contact-modal-container {
    padding: 24px 20px 20px;
    border-radius: 20px;
  }
  .contact-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  .contact-modal-title {
    font-size: 1.3rem;
  }
}
</style>
