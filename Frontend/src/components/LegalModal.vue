<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  initialTab: {
    type: String,
    default: 'terms', // 'terms' or 'privacy'
  },
})

const emit = defineEmits(['close'])

const activeTab = ref(props.initialTab)

watch(() => props.initialTab, (newVal) => {
  activeTab.value = newVal
})

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})

const handleKeydown = (e) => {
  if (e.key === 'Escape' && props.isOpen) {
    emit('close')
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})

const closeModal = () => {
  emit('close')
}
</script>

<template>
  <Teleport to="body">
    <Transition name="legal-modal">
      <div v-if="isOpen" class="legal-modal-overlay" @click.self="closeModal" role="dialog" aria-modal="true">
        <div class="legal-modal-card">
          <!-- Header -->
          <div class="legal-modal-header">
            <div class="legal-modal-tabs">
              <button
                type="button"
                class="legal-tab-btn"
                :class="{ 'active': activeTab === 'terms' }"
                @click="activeTab = 'terms'"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/>
                  <line x1="16" y1="17" x2="8" y2="17"/>
                  <polyline points="10 9 9 9 8 9"/>
                </svg>
                Terms of Service
              </button>
              <button
                type="button"
                class="legal-tab-btn"
                :class="{ 'active': activeTab === 'privacy' }"
                @click="activeTab = 'privacy'"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Privacy Policy
              </button>
            </div>

            <button type="button" class="legal-modal-close" @click="closeModal" aria-label="Close modal">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <!-- Body Content -->
          <div class="legal-modal-body">
            <!-- TERMS OF SERVICE -->
            <div v-if="activeTab === 'terms'" class="legal-content">
              <!-- <div class="legal-badge">Effective Date: July 24, 2026</div> -->
              <h2 class="legal-title">Terms of Service</h2>
              
              <div class="legal-section">
                <h3>1. Acceptance</h3>
                <p>By using Future Self, you agree to these Terms of Service.</p>
              </div>

              <div class="legal-section">
                <h3>2. Service</h3>
                <p>Future Self provides AI-powered conversations, journaling, goal planning, reflection, and personal growth tools.</p>
              </div>

              <div class="legal-section alert-section">
                <div class="alert-icon">⚠️</div>
                <div class="alert-content">
                  <h3>3. No Medical or Mental Health Advice</h3>
                  <p>Future Self is not a therapist, psychologist, psychiatrist, or medical provider. It does not provide medical, legal, or financial advice. If you are in crisis, contact local emergency services or a qualified professional immediately.</p>
                </div>
              </div>

              <div class="legal-section">
                <h3>4. Eligibility</h3>
                <p>Users must be at least 16 years old or meet the minimum legal age in their jurisdiction.</p>
              </div>

              <div class="legal-section">
                <h3>5. User Responsibilities</h3>
                <p>Use the platform lawfully, keep your account credentials secure, and do not misuse the service.</p>
              </div>

              <div class="legal-section">
                <h3>6. Prohibited Conduct</h3>
                <p>No illegal activities, abuse, reverse engineering, malware distribution, or attempts to bypass platform limits.</p>
              </div>

              <div class="legal-section">
                <h3>7. AI Responses</h3>
                <p>AI responses may occasionally be inaccurate or reflective of generative models and should not be treated as professional advice.</p>
              </div>

              <div class="legal-section">
                <h3>8. User Content</h3>
                <p>You retain ownership of your content while granting us permission to process it solely to operate and personalize the service.</p>
              </div>

              <div class="legal-section">
                <h3>9. Subscriptions</h3>
                <p>Premium features may require payment and renew automatically unless cancelled before the billing period ends.</p>
              </div>

              <div class="legal-section">
                <h3>10. Beta Features</h3>
                <p>Beta features are provided "as-is" and may change, evolve, or be discontinued at any time.</p>
              </div>

              <div class="legal-section">
                <h3>11. Availability</h3>
                <p>We do not guarantee uninterrupted or error-free service access.</p>
              </div>

              <div class="legal-section">
                <h3>12. Intellectual Property</h3>
                <p>All platform content, logos, designs, code, and branding belong exclusively to Future Self.</p>
              </div>

              <div class="legal-section">
                <h3>13. Termination</h3>
                <p>We reserve the right to suspend or terminate accounts that violate these Terms.</p>
              </div>

              <div class="legal-section">
                <h3>14. Limitation of Liability</h3>
                <p>Our liability is limited to the maximum extent permitted by applicable law.</p>
              </div>

              <div class="legal-section">
                <h3>15. Governing Law</h3>
                <p>These Terms are governed by the laws of the jurisdiction where Future Self operates.</p>
              </div>

              <div class="legal-section">
                <h3>16. Changes</h3>
                <p>We may update these Terms from time to time with notice posted on our platform.</p>
              </div>

              <div class="legal-section">
                <h3>17. Contact</h3>
                <p>If you have any questions regarding these Terms, contact us at <a href="mailto:aatmiksanghvi6@gmail.com" class="legal-link">aatmiksanghvi6@gmail.com</a>.</p>
              </div>
            </div>

            <!-- PRIVACY POLICY -->
            <div v-else class="legal-content">
              <!-- <div class="legal-badge">Effective Date: July 24, 2026</div> -->
              <h2 class="legal-title">Privacy Policy</h2>
              
              <div class="legal-section">
                <h3>1. Introduction</h3>
                <p>Welcome to Future Self ("we", "our", or "us"). Future Self is an AI-powered personal growth platform that provides reflective conversations, personalized guidance, goal tracking, and journaling experiences. Your privacy matters to us. This Privacy Policy explains what information we collect, why we collect it, how we use it, and your rights. By using Future Self, you agree to this Privacy Policy.</p>
              </div>

              <div class="legal-section">
                <h3>2. Information We Collect</h3>
                <ul class="legal-list">
                  <li><strong>Account Information:</strong> Name, email address, profile picture (optional), and authentication provider details.</li>
                  <li><strong>Onboarding Information:</strong> Goals, aspirations, challenges, values, desired traits, role models, communication preferences, and reflection responses.</li>
                  <li><strong>Conversation Data:</strong> Messages, AI responses, journal entries, feedback, and conversation history.</li>
                  <li><strong>Usage Information:</strong> Device type, browser, IP address, app version, crash logs, feature usage, and session duration.</li>
                  <li><strong>Payment Information:</strong> Payments are processed by secure third-party providers. We do not store credit card details.</li>
                </ul>
              </div>

              <div class="legal-section">
                <h3>3. How We Use Your Information</h3>
                <p>We use your information to personalize AI conversations, improve the platform, provide customer support, maintain conversation memory, prevent abuse, process subscriptions, and comply with legal obligations.</p>
              </div>

              <div class="legal-section">
                <h3>4. AI Processing</h3>
                <p>Your conversations may be processed by trusted AI providers solely to generate responses. We do not sell your data or use it for advertising.</p>
              </div>

              <div class="legal-section">
                <h3>5. Data Sharing</h3>
                <p>We share information only with authentication providers, AI providers, hosting infrastructure, analytics services, payment processors, and legal authorities when strictly required by law.</p>
              </div>

              <div class="legal-section">
                <h3>6. Data Retention</h3>
                <p>Your data is retained while your account remains active. You may request account and data deletion at any time.</p>
              </div>

              <div class="legal-section">
                <h3>7. Your Rights</h3>
                <p>You may request access to your data, correction, deletion, export of your data, or withdraw consent where applicable.</p>
              </div>

              <div class="legal-section">
                <h3>8. Security</h3>
                <p>We implement industry-standard security measures including end-to-end transport encryption, access controls, and secure database infrastructure.</p>
              </div>

              <div class="legal-section">
                <h3>9. Children's Privacy</h3>
                <p>Future Self is intended for users aged 16+ or the minimum digital consent age in their jurisdiction.</p>
              </div>

              <div class="legal-section">
                <h3>10. Cookies</h3>
                <p>We use session cookies and local storage for authentication, user preferences, analytics, and app performance.</p>
              </div>

              <div class="legal-section">
                <h3>11. Changes</h3>
                <p>We may update this Privacy Policy periodically. Significant changes will be announced on our platform.</p>
              </div>

              <div class="legal-section">
                <h3>12. Contact</h3>
                <p>For privacy inquiries, data requests, or support, contact us at <a href="mailto:aatmiksanghvi6@gmail.com" class="legal-link">aatmiksanghvi6@gmail.com</a>.</p>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="legal-modal-footer">
            <router-link
              :to="activeTab === 'terms' ? '/terms-of-service' : '/privacy-policy'"
              class="legal-fullpage-link"
              @click="closeModal"
            >
              Open in full page ↗
            </router-link>
            <button type="button" class="legal-close-btn" @click="closeModal">
              Close
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.legal-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: rgba(15, 12, 26, 0.75);
  backdrop-filter: blur(12px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.legal-modal-card {
  background: linear-gradient(160deg, rgba(26, 16, 48, 0.95) 0%, rgba(13, 31, 60, 0.95) 100%);
  border: 1px solid rgba(167, 139, 250, 0.25);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 30px rgba(124, 58, 237, 0.15);
  border-radius: 20px;
  width: 100%;
  max-width: 720px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  color: #ffffff;
  overflow: hidden;
}

.legal-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(15, 12, 26, 0.4);
}

.legal-modal-tabs {
  display: flex;
  gap: 8px;
  background: rgba(255, 255, 255, 0.05);
  padding: 4px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.legal-tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.6);
  background: transparent;
  transition: all 0.2s ease;
}

.legal-tab-btn:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.05);
}

.legal-tab-btn.active {
  background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
  color: #ffffff;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
}

.legal-modal-close {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.7);
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.legal-modal-close:hover {
  background: rgba(255, 255, 255, 0.15);
  color: #ffffff;
  transform: scale(1.05);
}

.legal-modal-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}

.legal-modal-body::-webkit-scrollbar {
  width: 6px;
}

.legal-modal-body::-webkit-scrollbar-thumb {
  background: rgba(167, 139, 250, 0.25);
  border-radius: 3px;
}

.legal-badge {
  display: inline-block;
  padding: 4px 12px;
  background: rgba(124, 58, 237, 0.15);
  border: 1px solid rgba(167, 139, 250, 0.3);
  color: #a78bfa;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  margin-bottom: 12px;
}

.legal-title {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 20px;
  background: linear-gradient(135deg, #ffffff 0%, #a78bfa 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.legal-section {
  margin-bottom: 20px;
  line-height: 1.6;
}

.legal-section h3 {
  font-size: 15px;
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 6px;
}

.legal-section p {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.7);
}

.alert-section {
  background: rgba(251, 191, 36, 0.08);
  border: 1px solid rgba(251, 191, 36, 0.3);
  border-radius: 12px;
  padding: 16px;
  display: flex;
  gap: 12px;
}

.alert-icon {
  font-size: 20px;
}

.alert-content h3 {
  color: #fbbf24;
}

.legal-list {
  padding-left: 20px;
  font-size: 14px;
  color: rgba(255, 255, 255, 0.7);
}

.legal-list li {
  margin-bottom: 8px;
}

.legal-link {
  color: #a78bfa;
  text-decoration: underline;
}

.legal-modal-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(15, 12, 26, 0.4);
}

.legal-fullpage-link {
  font-size: 13px;
  color: #a78bfa;
  transition: color 0.2s ease;
}

.legal-fullpage-link:hover {
  color: #ffffff;
  text-decoration: underline;
}

.legal-close-btn {
  padding: 8px 20px;
  background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
  color: #ffffff;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s ease;
}

.legal-close-btn:hover {
  box-shadow: 0 4px 12px rgba(124, 58, 237, 0.4);
  transform: translateY(-1px);
}

/* Modal Animations */
.legal-modal-enter-active,
.legal-modal-leave-active {
  transition: opacity 0.25s ease;
}

.legal-modal-enter-active .legal-modal-card,
.legal-modal-leave-active .legal-modal-card {
  transition: transform 0.25s ease, opacity 0.25s ease;
}

.legal-modal-enter-from,
.legal-modal-leave-to {
  opacity: 0;
}

.legal-modal-enter-from .legal-modal-card {
  transform: scale(0.95) translateY(10px);
}

.legal-modal-leave-to .legal-modal-card {
  transform: scale(0.95) translateY(10px);
}
</style>
