<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Navbar from '@/components/Navbar.vue'
import FooterSection from '@/components/FooterSection.vue'
import contactService from '@/services/contactService'

const router = useRouter()
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

onMounted(() => {
  if (auth.user) {
    if (auth.user.name) form.name = auth.user.name
    if (auth.user.email) form.email = auth.user.email
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

    await contactService.submitContact(payload)
    isSubmitted.value = true
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

function resetForm() {
  isSubmitted.value = false
  form.subject = ''
  form.message = ''
  form.category = 'General Inquiry'
  if (auth.user) {
    if (auth.user.name) form.name = auth.user.name
    if (auth.user.email) form.email = auth.user.email
  }
}
</script>

<template>
  <div class="contact-page-wrapper">
    <Navbar />

    <main class="contact-main">
      <div class="contact-container">
        
        <!-- Header Banner -->
        <div class="contact-header">
          <div class="contact-hero-badge">
            <span class="badge-dot"></span>
            Support & Queries
          </div>
          <h1 class="contact-hero-title">How Can We Help You?</h1>
          <p class="contact-hero-subtitle">
            Have questions, feedback, or need technical assistance with your Future Self journey?
            Send us a message and our team will get back to you promptly.
          </p>
        </div>

        <!-- 2 Column Layout -->
        <div class="contact-layout-grid">
          
          <!-- Left Info Cards -->
          <div class="contact-info-col">
            <div class="info-card">
              <div class="info-card-icon">✉️</div>
              <div class="info-card-content">
                <h3 class="info-card-title">Email Us Directly</h3>
                <p class="info-card-desc">For inquiries, partnerships, or direct support.</p>
                <a href="mailto:support@futureself.in" class="info-card-link">support@futureself.in</a>
              </div>
            </div>

            <div class="info-card">
              <div class="info-card-icon">⚡</div>
              <div class="info-card-content">
                <h3 class="info-card-title">Quick Response Time</h3>
                <p class="info-card-desc">We review messages every business day and respond within 24 hours.</p>
                <span class="info-card-status">● Live Support Mon-Fri</span>
              </div>
            </div>

            <div class="info-card">
              <div class="info-card-icon">🔒</div>
              <div class="info-card-content">
                <h3 class="info-card-title">Privacy Guaranteed</h3>
                <p class="info-card-desc">Your queries and personal data are strictly encrypted and confidential.</p>
                <router-link to="/privacy-policy" class="info-card-link">View Privacy Policy →</router-link>
              </div>
            </div>

            <div class="info-faq-box">
              <div class="faq-box-icon">💡</div>
              <div>
                <h4 class="faq-box-title">Looking for common answers?</h4>
                <p class="faq-box-desc">Check out our frequently asked questions on our homepage.</p>
                <a href="/#faq" class="faq-box-link">Explore FAQ Section →</a>
              </div>
            </div>
          </div>

          <!-- Right Form Card -->
          <div class="contact-card">
            
            <!-- Success State -->
            <div v-if="isSubmitted" class="contact-page-success">
              <div class="success-icon-badge">✨</div>
              <h2 class="success-title">Message Received!</h2>
              <p class="success-desc">
                Thank you, <strong>{{ form.name }}</strong>! We have received your query. Our team will review the details and respond back to <strong>{{ form.email }}</strong> as soon as possible.
              </p>
              <div class="success-actions">
                <button class="btn-primary" @click="resetForm">
                  Send Another Query
                </button>
                <router-link to="/" class="btn-secondary">
                  Back to Dashboard
                </router-link>
              </div>
            </div>

            <!-- Form State -->
            <div v-else>
              <h2 class="form-title">Send a Query</h2>
              <p class="form-subtitle">Fill in the form below and we'll be in touch.</p>

              <div v-if="errorMessage" class="error-banner">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ errorMessage }}</span>
              </div>

              <form @submit.prevent="handleSubmit" class="form-element">
                <!-- Category Selector -->
                <div class="form-group">
                  <label class="form-label">Query Category</label>
                  <div class="category-chips">
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

                <div class="form-row-2">
                  <!-- Name -->
                  <div class="form-group">
                    <label class="form-label" for="page-contact-name">
                      Full Name <span class="required">*</span>
                    </label>
                    <input
                      id="page-contact-name"
                      v-model="form.name"
                      type="text"
                      class="form-input"
                      :class="{ 'has-error': errors.name }"
                      placeholder="Your full name"
                    />
                    <span v-if="errors.name" class="field-error">{{ errors.name }}</span>
                  </div>

                  <!-- Email -->
                  <div class="form-group">
                    <label class="form-label" for="page-contact-email">
                      Email Address <span class="required">*</span>
                    </label>
                    <input
                      id="page-contact-email"
                      v-model="form.email"
                      type="email"
                      class="form-input"
                      :class="{ 'has-error': errors.email }"
                      placeholder="you@example.com"
                    />
                    <span v-if="errors.email" class="field-error">{{ errors.email }}</span>
                  </div>
                </div>

                <!-- Subject -->
                <div class="form-group">
                  <label class="form-label" for="page-contact-subject">
                    Subject <span class="required">*</span>
                  </label>
                  <input
                    id="page-contact-subject"
                    v-model="form.subject"
                    type="text"
                    class="form-input"
                    :class="{ 'has-error': errors.subject }"
                    placeholder="What is your query regarding?"
                  />
                  <span v-if="errors.subject" class="field-error">{{ errors.subject }}</span>
                </div>

                <!-- Message -->
                <div class="form-group">
                  <div class="label-with-counter">
                    <label class="form-label" for="page-contact-message">
                      Your Message <span class="required">*</span>
                    </label>
                    <span class="counter-text">{{ form.message.length }} / 3000</span>
                  </div>
                  <textarea
                    id="page-contact-message"
                    v-model="form.message"
                    rows="5"
                    maxlength="3000"
                    class="form-textarea"
                    :class="{ 'has-error': errors.message }"
                    placeholder="Describe your question or issue in detail..."
                  ></textarea>
                  <span v-if="errors.message" class="field-error">{{ errors.message }}</span>
                </div>

                <button
                  type="submit"
                  class="btn-submit"
                  :disabled="isSubmitting"
                  id="btn-page-contact-submit"
                >
                  <span v-if="isSubmitting" class="spinner-dot"></span>
                  <span v-if="isSubmitting">Sending Query...</span>
                  <span v-else>Send Query 🚀</span>
                </button>
              </form>
            </div>

          </div>
        </div>

      </div>
    </main>

    <FooterSection />
  </div>
</template>

<style scoped>
.contact-page-wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #090514;
  color: #fff;
  position: relative;
  overflow-x: hidden;
}

.contact-main {
  flex: 1;
  padding: 120px 24px 80px;
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
  box-sizing: border-box;
}

.contact-container {
  width: 100%;
}

/* ── Hero Header ─────────────────────────────────── */
.contact-header {
  text-align: center;
  max-width: 720px;
  margin: 0 auto 52px;
}

.contact-hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 16px;
  background: rgba(139, 92, 246, 0.15);
  border: 1px solid rgba(139, 92, 246, 0.35);
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
  color: #c4b5fd;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  margin-bottom: 18px;
}

.badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #a78bfa;
  box-shadow: 0 0 8px #a78bfa;
}

.contact-hero-title {
  font-size: 2.75rem;
  font-weight: 800;
  color: #fff;
  margin: 0 0 16px;
  line-height: 1.15;
  background: linear-gradient(135deg, #fff 40%, #c4b5fd 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.contact-hero-subtitle {
  font-size: 1.05rem;
  color: rgba(203, 213, 225, 0.85);
  line-height: 1.6;
  margin: 0;
}

/* ── Layout Grid ─────────────────────────────────── */
.contact-layout-grid {
  display: grid;
  grid-template-columns: 1fr 1.4fr;
  gap: 36px;
  align-items: flex-start;
}

/* ── Left Info Column ────────────────────────────── */
.contact-info-col {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.info-card {
  display: flex;
  gap: 16px;
  padding: 22px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(167, 139, 250, 0.18);
  border-radius: 20px;
  transition: all 0.25s ease;
}

.info-card:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(167, 139, 250, 0.35);
  transform: translateY(-2px);
}

.info-card-icon {
  font-size: 1.5rem;
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(139, 92, 246, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.info-card-content {
  flex: 1;
}

.info-card-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #f8fafc;
  margin: 0 0 6px;
}

.info-card-desc {
  font-size: 0.85rem;
  color: rgba(203, 213, 225, 0.75);
  line-height: 1.45;
  margin: 0 0 8px;
}

.info-card-link {
  font-size: 0.85rem;
  color: #a78bfa;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.15s ease;
}

.info-card-link:hover {
  color: #c4b5fd;
  text-decoration: underline;
}

.info-card-status {
  font-size: 0.8rem;
  color: #34d399;
  font-weight: 500;
}

.info-faq-box {
  display: flex;
  gap: 14px;
  padding: 18px 22px;
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(79, 70, 229, 0.08));
  border: 1px dashed rgba(167, 139, 250, 0.3);
  border-radius: 18px;
}

.faq-box-icon {
  font-size: 1.4rem;
}

.faq-box-title {
  font-size: 0.95rem;
  font-weight: 600;
  margin: 0 0 4px;
  color: #e2e8f0;
}

.faq-box-desc {
  font-size: 0.82rem;
  color: rgba(203, 213, 225, 0.7);
  margin: 0 0 6px;
}

.faq-box-link {
  font-size: 0.82rem;
  color: #c4b5fd;
  font-weight: 600;
  text-decoration: none;
}

.faq-box-link:hover {
  text-decoration: underline;
}

/* ── Right Form Card ─────────────────────────────── */
.contact-card {
  background: linear-gradient(145deg, rgba(26, 20, 50, 0.85), rgba(15, 12, 32, 0.92));
  border: 1px solid rgba(167, 139, 250, 0.22);
  border-radius: 24px;
  padding: 36px;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.5), 0 0 40px rgba(124, 58, 237, 0.18);
}

.form-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 6px;
  color: #f8fafc;
}

.form-subtitle {
  font-size: 0.88rem;
  color: rgba(203, 213, 225, 0.75);
  margin: 0 0 24px;
}

.error-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.35);
  border-radius: 12px;
  color: #fca5a5;
  font-size: 0.85rem;
  margin-bottom: 20px;
}

.form-element {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: rgba(226, 232, 240, 0.9);
}

.required {
  color: #f87171;
}

.label-with-counter {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.counter-text {
  font-size: 0.75rem;
  color: rgba(148, 163, 184, 0.7);
}

.category-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 4px;
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
}

.category-chip:hover {
  background: rgba(139, 92, 246, 0.12);
  border-color: rgba(139, 92, 246, 0.3);
  color: #fff;
}

.category-chip.active {
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.35), rgba(79, 70, 229, 0.3));
  border-color: #8b5cf6;
  color: #fff;
  box-shadow: 0 0 12px rgba(139, 92, 246, 0.3);
}

.form-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-input,
.form-textarea {
  width: 100%;
  box-sizing: border-box;
  background: rgba(15, 12, 26, 0.7);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 12px;
  padding: 12px 16px;
  color: #f8fafc;
  font-size: 0.9rem;
  font-family: inherit;
  outline: none;
  transition: all 0.2s ease;
}

.form-input:focus,
.form-textarea:focus {
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.22);
  background: rgba(15, 12, 26, 0.9);
}

.form-input.has-error,
.form-textarea.has-error {
  border-color: #f87171;
  background: rgba(239, 68, 68, 0.05);
}

.field-error {
  font-size: 0.75rem;
  color: #f87171;
}

.form-textarea {
  resize: vertical;
  min-height: 110px;
}

.btn-submit {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 14px 28px;
  background: linear-gradient(135deg, #7c3aed, #6366f1);
  border: none;
  border-radius: 14px;
  color: #fff;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 6px 20px rgba(124, 58, 237, 0.35);
  transition: all 0.2s ease;
  margin-top: 6px;
}

.btn-submit:hover:not(:disabled) {
  background: linear-gradient(135deg, #8b5cf6, #4f46e5);
  box-shadow: 0 8px 24px rgba(124, 58, 237, 0.45);
  transform: translateY(-2px);
}

.btn-submit:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none;
}

.spinner-dot {
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

/* ── Success Page State ──────────────────────────── */
.contact-page-success {
  text-align: center;
  padding: 40px 16px;
}

.success-icon-badge {
  font-size: 2.8rem;
  margin-bottom: 16px;
}

.success-title {
  font-size: 1.85rem;
  font-weight: 700;
  margin: 0 0 12px;
  color: #f8fafc;
}

.success-desc {
  font-size: 1rem;
  color: rgba(203, 213, 225, 0.85);
  line-height: 1.6;
  max-width: 480px;
  margin: 0 auto 32px;
}

.success-actions {
  display: flex;
  gap: 14px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-primary {
  padding: 12px 24px;
  background: linear-gradient(135deg, #7c3aed, #6366f1);
  border: none;
  border-radius: 12px;
  color: #fff;
  font-size: 0.92rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #8b5cf6, #4f46e5);
  transform: translateY(-1px);
}

.btn-secondary {
  padding: 12px 24px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 12px;
  color: #e2e8f0;
  font-size: 0.92rem;
  font-weight: 600;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.15);
  color: #fff;
}

/* ── Responsive ──────────────────────────────────── */
@media (max-width: 900px) {
  .contact-layout-grid {
    grid-template-columns: 1fr;
  }
  .contact-hero-title {
    font-size: 2.2rem;
  }
}

@media (max-width: 600px) {
  .contact-card {
    padding: 24px 18px;
  }
  .form-row-2 {
    grid-template-columns: 1fr;
  }
  .contact-hero-title {
    font-size: 1.85rem;
  }
}
</style>
