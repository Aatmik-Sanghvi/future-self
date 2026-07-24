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
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
          </div>
          <!-- <div class="legal-badge">Effective Date: July 24, 2026</div> -->
          <h1 class="page-title">Terms of Service</h1>
          <p class="page-subtitle">Rules, responsibilities, and terms governing your use of Future Self</p>
        </div>

        <!-- Document Content -->
        <div class="document-body">
          <section class="doc-section">
            <h2>1. Acceptance</h2>
            <p>By using Future Self, you agree to these Terms of Service. Please read them carefully before using our platform.</p>
          </section>

          <section class="doc-section">
            <h2>2. Service Overview</h2>
            <p>Future Self provides AI-powered conversations, journaling, goal planning, reflection, and personal growth tools designed to support your personal development journey.</p>
          </section>

          <section class="doc-section alert-section">
            <div class="alert-icon">⚠️</div>
            <div class="alert-content">
              <h2>3. No Medical or Mental Health Advice</h2>
              <p>Future Self is not a therapist, psychologist, psychiatrist, or medical provider. It does not provide medical, legal, or financial advice. If you are in crisis or experiencing a health emergency, please contact local emergency services or a qualified healthcare professional immediately.</p>
            </div>
          </section>

          <section class="doc-section">
            <h2>4. Eligibility</h2>
            <p>Users must be at least 16 years old or meet the minimum legal digital consent age in their jurisdiction to create an account and use the service.</p>
          </section>

          <section class="doc-section">
            <h2>5. User Responsibilities</h2>
            <p>You agree to use the platform lawfully, maintain the confidentiality of your account login credentials, and immediately notify us of any unauthorized account activity.</p>
          </section>

          <section class="doc-section">
            <h2>6. Prohibited Conduct</h2>
            <p>You agree not to engage in illegal activities, harassment, reverse engineering, distributing malicious code, or attempting to breach or bypass platform limits and security.</p>
          </section>

          <section class="doc-section">
            <h2>7. AI Responses</h2>
            <p>AI responses are generated algorithmically, may occasionally contain inaccuracies, and should not be treated as objective factual truth or professional advice.</p>
          </section>

          <section class="doc-section">
            <h2>8. User Content</h2>
            <p>You retain full ownership of your content, journal entries, and messages while granting Future Self permission to process it solely to operate and personalize the platform experience.</p>
          </section>

          <section class="doc-section">
            <h2>9. Subscriptions & Billing</h2>
            <p>Access to premium features may require a paid subscription. Subscriptions renew automatically unless cancelled prior to the end of the current billing period.</p>
          </section>

          <section class="doc-section">
            <h2>10. Beta Features</h2>
            <p>Experimental or beta features are provided "as-is" for testing purposes and may be modified, suspended, or discontinued at any time without notice.</p>
          </section>

          <section class="doc-section">
            <h2>11. Service Availability</h2>
            <p>We strive to maintain high availability but do not guarantee uninterrupted, error-free, or continuous operation of the platform.</p>
          </section>

          <section class="doc-section">
            <h2>12. Intellectual Property</h2>
            <p>All software code, designs, logos, trademarks, and branding associated with Future Self are the exclusive intellectual property of Future Self.</p>
          </section>

          <section class="doc-section">
            <h2>13. Termination</h2>
            <p>We reserve the right to suspend or terminate accounts that violate these Terms or engage in abusive platform behavior.</p>
          </section>

          <section class="doc-section">
            <h2>14. Limitation of Liability</h2>
            <p>To the maximum extent permitted by law, Future Self shall not be liable for indirect, incidental, or consequential damages resulting from your use of the platform.</p>
          </section>

          <section class="doc-section">
            <h2>15. Governing Law</h2>
            <p>These Terms are governed by and construed in accordance with the laws of the operating jurisdiction of Future Self.</p>
          </section>

          <section class="doc-section">
            <h2>16. Changes to Terms</h2>
            <p>We reserve the right to modify these Terms at any time. Continued use of the service following notice of changes constitutes acceptance.</p>
          </section>

          <section class="doc-section">
            <h2>17. Contact Us</h2>
            <p>If you have any questions regarding these Terms, please reach out to <a href="mailto:aatmiksanghvi6@gmail.com" class="doc-link">aatmiksanghvi6@gmail.com</a>.</p>
          </section>
        </div>

        <!-- Footer -->
        <div class="legal-footer">
          <span>Curious about data safety?</span>
          <router-link to="/privacy-policy" class="footer-link">Read Privacy Policy →</router-link>
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

.alert-section {
  background: rgba(251, 191, 36, 0.08);
  border: 1px solid rgba(251, 191, 36, 0.3);
  border-radius: 16px;
  padding: 20px;
  display: flex;
  gap: 16px;
}

.alert-icon {
  font-size: 24px;
}

.alert-content h2 {
  color: #fbbf24;
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
