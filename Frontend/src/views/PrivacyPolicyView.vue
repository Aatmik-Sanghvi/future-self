<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const goBack = () => {
  if (window.history.length > 1) {
    router.back()
  } else {
    router.push('/')
  }
}
</script>

<template>
  <div class="legal-page">
    <div class="legal-container">
      <!-- Nav back button -->
      <button type="button" class="back-btn" @click="goBack">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"/>
          <polyline points="12 19 5 12 12 5"/>
        </svg>
        Back
      </button>

      <div class="legal-card">
        <!-- Header -->
        <div class="legal-header">
          <div class="legal-icon-wrap">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
          </div>
          <!-- <div class="legal-badge">Effective Date: July 24, 2026</div> -->
          <h1 class="page-title">Privacy Policy</h1>
          <p class="page-subtitle">Learn how Future Self protects and handles your personal data</p>
        </div>

        <!-- Document Content -->
        <div class="document-body">
          <section class="doc-section">
            <h2>1. Introduction</h2>
            <p>Welcome to Future Self ("we", "our", or "us"). Future Self is an AI-powered personal growth platform that provides reflective conversations, personalized guidance, goal tracking, and journaling experiences.</p>
            <p>Your privacy matters deeply to us. This Privacy Policy explains what information we collect, why we collect it, how we use it, and your rights regarding your data. By using Future Self, you agree to this Privacy Policy.</p>
          </section>

          <section class="doc-section">
            <h2>2. Information We Collect</h2>
            <div class="data-grid">
              <div class="data-card">
                <h3>Account Information</h3>
                <p>Name, email address, profile picture (optional), and authentication provider details.</p>
              </div>
              <div class="data-card">
                <h3>Onboarding Information</h3>
                <p>Goals, aspirations, challenges, values, desired traits, role models, communication preferences, and reflection responses.</p>
              </div>
              <div class="data-card">
                <h3>Conversation Data</h3>
                <p>Messages, AI responses, journal entries, feedback, and conversation history.</p>
              </div>
              <div class="data-card">
                <h3>Usage Information</h3>
                <p>Device type, browser, IP address, app version, crash logs, feature usage, and session duration.</p>
              </div>
              <div class="data-card">
                <h3>Payment Information</h3>
                <p>Payments are processed by secure third-party payment providers. We do not store card details.</p>
              </div>
            </div>
          </section>

          <section class="doc-section">
            <h2>3. How We Use Your Information</h2>
            <p>We use your information to personalize AI conversations, improve the platform, provide customer support, maintain conversation memory, prevent abuse, process subscriptions, and comply with legal obligations.</p>
          </section>

          <section class="doc-section">
            <h2>4. AI Processing</h2>
            <p>Your conversations may be processed by trusted AI providers solely to generate responses. We do not sell your data or use it for advertising.</p>
          </section>

          <section class="doc-section">
            <h2>5. Data Sharing</h2>
            <p>We share information only with authentication providers, AI providers, hosting infrastructure, analytics services, payment processors, and legal authorities when strictly required by law.</p>
          </section>

          <section class="doc-section">
            <h2>6. Data Retention</h2>
            <p>Your data is retained while your account remains active. You may request account and data deletion at any time.</p>
          </section>

          <section class="doc-section">
            <h2>7. Your Rights</h2>
            <p>You may request access to your data, correction, deletion, export of your data, or withdraw consent where applicable.</p>
          </section>

          <section class="doc-section">
            <h2>8. Security</h2>
            <p>We implement industry-standard security measures including encryption in transit and at rest, strict access controls, and secure server infrastructure.</p>
          </section>

          <section class="doc-section">
            <h2>9. Children's Privacy</h2>
            <p>Future Self is intended for users aged 16+ or the minimum digital consent age in their jurisdiction.</p>
          </section>

          <section class="doc-section">
            <h2>10. Cookies</h2>
            <p>We use session cookies and local storage for authentication, user preferences, analytics, and app performance.</p>
          </section>

          <section class="doc-section">
            <h2>11. Changes</h2>
            <p>We may update this Privacy Policy from time to time. Significant changes will be announced on our platform.</p>
          </section>

          <section class="doc-section">
            <h2>12. Contact</h2>
            <p>For questions or requests regarding your privacy, contact us at <a href="mailto:aatmiksanghvi6@gmail.com" class="doc-link">aatmiksanghvi6@gmail.com</a>.</p>
          </section>
        </div>

        <!-- Footer -->
        <div class="legal-footer">
          <span>Need to check our Terms?</span>
          <router-link to="/terms-of-service" class="footer-link">Read Terms of Service →</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.legal-page {
  min-height: 100vh;
  padding: 40px 20px;
  background: linear-gradient(160deg, #0f0c1a 0%, #1a1030 35%, #0d1f3c 100%);
  color: #ffffff;
  display: flex;
  justify-content: center;
}

.legal-container {
  width: 100%;
  max-width: 860px;
}

.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: rgba(255, 255, 255, 0.8);
  padding: 8px 16px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 24px;
  transition: all 0.2s ease;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
  transform: translateX(-2px);
}

.legal-card {
  background: rgba(26, 16, 48, 0.7);
  border: 1px solid rgba(167, 139, 250, 0.2);
  backdrop-filter: blur(16px);
  border-radius: 24px;
  padding: 40px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
}

.legal-header {
  text-align: center;
  margin-bottom: 40px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  padding-bottom: 32px;
}

.legal-icon-wrap {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.2) 0%, rgba(79, 70, 229, 0.2) 100%);
  border: 1px solid rgba(167, 139, 250, 0.3);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #a78bfa;
  margin-bottom: 16px;
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

.page-title {
  font-size: 32px;
  font-weight: 800;
  margin-bottom: 8px;
  background: linear-gradient(135deg, #ffffff 0%, #a78bfa 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.page-subtitle {
  font-size: 15px;
  color: rgba(255, 255, 255, 0.6);
}

.document-body {
  line-height: 1.7;
}

.doc-section {
  margin-bottom: 32px;
}

.doc-section h2 {
  font-size: 18px;
  font-weight: 700;
  color: #e2e8f0;
  margin-bottom: 10px;
}

.doc-section p {
  font-size: 15px;
  color: rgba(255, 255, 255, 0.75);
  margin-bottom: 12px;
}

.data-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 16px;
  margin-top: 16px;
}

.data-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 16px;
}

.data-card h3 {
  font-size: 14px;
  font-weight: 600;
  color: #a78bfa;
  margin-bottom: 6px;
}

.data-card p {
  font-size: 13.5px;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 0;
}

.doc-link {
  color: #a78bfa;
  text-decoration: underline;
}

.legal-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding-top: 24px;
  margin-top: 40px;
  font-size: 14px;
  color: rgba(255, 255, 255, 0.6);
}

.footer-link {
  color: #a78bfa;
  font-weight: 600;
  transition: color 0.2s ease;
}

.footer-link:hover {
  color: #ffffff;
}

@media (max-width: 640px) {
  .legal-card {
    padding: 24px;
  }

  .page-title {
    font-size: 24px;
  }
}
</style>
