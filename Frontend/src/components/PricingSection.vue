<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import paymentService from '@/services/paymentService';

const router = useRouter();
const auth = useAuthStore();
const isProcessing = ref(false);
const planPrice = ref('₹186');

onMounted(async () => {
  try {
    const res = await paymentService.getPlan();
    if (res.data?.data?.amount_formatted) {
      planPrice.value = res.data.data.amount_formatted;
    }
  } catch (e) {
    // Graceful fallback to default
  }
});

const startMyJourney = () => {
  router.push({ name: 'Login', query: { redirect: '/#pricing' } });
}

const navigateToChat = () => {
  router.push({ name: 'Chat' });
}

const handleProAction = async () => {
  if (!auth.isAuthenticated) {
    startMyJourney();
    return;
  }

  if (auth.isPro) {
    router.push({ name: 'Missions' });
    return;
  }

  isProcessing.value = true;

  try {
    // 1. Ensure Razorpay SDK script is loaded
    await paymentService.loadRazorpayScript();

    // 2. Create Razorpay subscription on backend (includes 7-day trial if eligible)
    const response = await paymentService.createSubscription();
    const subData = response.data?.data;

    if (!subData || !subData.subscription_id) {
      throw new Error('Failed to initiate subscription. Please try again.');
    }

    const isTrial = subData.is_trial;
    const formattedPrice = subData.amount ? `₹${Math.round(subData.amount / 100)}` : planPrice.value;

    // 3. Configure Razorpay checkout modal with subscription_id
    const options = {
      key: subData.key,
      subscription_id: subData.subscription_id,
      name: 'FutureSelf',
      description: isTrial
        ? `Pro Monthly — 7-Day Free Trial (${formattedPrice}/month after trial)`
        : `Pro Monthly Subscription (${formattedPrice}/month)`,
      image: '/favicon.ico',
      prefill: {
        name: auth.user?.name || '',
        email: auth.user?.email || '',
        contact: auth.user?.mobile || '',
      },
      theme: {
        color: '#7c3aed',
      },
      handler: async function (paymentResponse) {
        try {
          // 4. Verify subscription authorization signature on backend
          await paymentService.verifySubscription({
            razorpay_subscription_id: paymentResponse.razorpay_subscription_id,
            razorpay_payment_id: paymentResponse.razorpay_payment_id,
            razorpay_signature: paymentResponse.razorpay_signature,
          });

          // 5. Refresh user state in auth store
          await auth.fetchUser();
          auth.toastMessage(
            isTrial
              ? '🎉 7-day free trial activated! Daily Missions and Goal Tracking are now unlocked.'
              : '🎉 Welcome to FutureSelf Pro! Your subscription is now active.',
            'success'
          );
          router.push({ name: 'Missions' });
        } catch (err) {
          auth.toastMessage(err.response?.data?.message || 'Subscription verification failed.', 'error');
        } finally {
          isProcessing.value = false;
        }
      },
      modal: {
        ondismiss: function () {
          isProcessing.value = false;
        },
      },
    };

    const rzp = new window.Razorpay(options);
    rzp.on('payment.failed', function (failResponse) {
      auth.toastMessage(failResponse.error?.description || 'Card authorization failed. Please try again.', 'error');
      isProcessing.value = false;
    });

    rzp.open();
  } catch (err) {
    auth.toastMessage(err.response?.data?.message || err.message || 'Unable to start checkout. Please try again.', 'error');
    isProcessing.value = false;
  }
};
</script>

<template>
    <section class="pricing-section" id="pricing">
        <div class="container">
            <div class="pricing-header">
                <span class="section-label">Pricing</span>
                <h2 class="section-title">Invest in who you're becoming</h2>
                <p class="section-sub">Start free forever, or try Pro free for 7 days.</p>
            </div>
            <div class="pricing-grid">
                <!-- ── FREE TIER ── -->
                <div class="pricing-card">
                    <div class="pricing-card-header">
                        <div class="pricing-name">Free</div>
                    </div>
                    <div class="pricing-price">
                        <span class="pricing-amount">₹0</span>
                        <span class="pricing-period">/month</span>
                    </div>
                    <p class="pricing-tagline">Free forever</p>
                    <div class="pricing-features">
                        <div class="pricing-feature">
                            <div class="pricing-check">✓</div>
                            <span>Limited personalised chats every day</span>
                        </div>
                        <div class="pricing-feature">
                            <div class="pricing-check">✓</div>
                            <span>Complete onboarding & personality profile</span>
                        </div>
                        <div class="pricing-feature">
                            <div class="pricing-check">✓</div>
                            <span>Full conversation history</span>
                        </div>
                    </div>
                    <button class="btn-plan secondary" @click="startMyJourney()" v-if="!auth.isAuthenticated">Get Started Free</button>
                    <button class="btn-plan secondary" @click="navigateToChat()" v-else>Start Chatting</button>
                    <p class="pricing-note">🔒 No payment required</p>
                </div>

                <!-- ── PAID TIER ── -->
                <div class="pricing-card highlight">
                    <div class="pricing-badges">
                        <span class="pricing-badge">Most Popular</span>
                        <span class="pricing-badge trial">7-Day Free Trial</span>
                    </div>
                    <div class="pricing-card-header">
                        <div class="pricing-name">Pro</div>
                    </div>
                    <div class="pricing-price">
                        <span class="pricing-amount">{{ planPrice }}</span>
                        <span class="pricing-period">/month</span>
                    </div>
                    <p class="pricing-tagline">
                        <template v-if="auth.isAuthenticated && auth.hasUsedTrial && !auth.isPro">
                            Your 7-day free trial has ended. Subscribe to unlock Daily Missions & Goal Tracking.
                        </template>
                        <template v-else-if="auth.isAuthenticated && auth.isPro && auth.isTrial">
                            Your 7-day free trial is active. Enjoy Daily Missions & Goal Tracking!
                        </template>
                        <template v-else-if="auth.isAuthenticated && auth.isPro">
                            Your Pro plan is active. Unlimited access to Missions & Goal Tracking.
                        </template>
                        <template v-else>
                            Enjoy 7 days of Pro free. Cancel anytime.
                        </template>
                    </p>
                    <div class="pricing-features">
                        <div class="pricing-feature included-free">
                            <div class="pricing-check">✓</div>
                            <span>Everything in Free</span>
                        </div>
                        <div class="pricing-divider"></div>
                        <div class="pricing-feature">
                            <div class="pricing-check star">★</div>
                            <span>Personalized daily missions to keep you moving forward</span>
                        </div>
                        <div class="pricing-feature">
                            <div class="pricing-check star">★</div>
                            <span>Track progress toward your goals</span>
                        </div>
                    </div>
                    <button
                        class="btn-plan primary"
                        :disabled="isProcessing"
                        @click="handleProAction()"
                        id="btn-pro-action"
                    >
                        <span v-if="isProcessing">Please wait...</span>
                        <span v-else-if="!auth.isAuthenticated">Start 7-Day Free Trial</span>
                        <span v-else-if="auth.isPro">✨ You're on Pro — Go to Missions</span>
                        <span v-else-if="!auth.hasUsedTrial">Try Pro Free for 7 Days</span>
                        <span v-else>Subscribe to Pro — {{ planPrice }}/month</span>
                    </button>
                    <p class="pricing-note">
                        <template v-if="auth.isAuthenticated && auth.hasUsedTrial && !auth.isPro">
                            7-day trial already used. Subscribe to regain access to Daily Missions & Goal Tracking.
                        </template>
                        <template v-else-if="auth.isAuthenticated && auth.isPro && auth.isTrial">
                            Active 7-day free trial. Cancel anytime in your profile settings.
                        </template>
                        <template v-else>
                            Free for 7 days, then {{ planPrice }}/month. Cancel anytime.
                        </template>
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.pricing-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24px;
  max-width: 720px;
  margin: 0 auto;
}

.pricing-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.pricing-price {
  display: flex;
  align-items: baseline;
  gap: 6px;
  margin-bottom: 4px;
}

.pricing-amount {
  font-size: 38px;
  font-weight: 900;
  color: #ffffff;
  line-height: 1;
}

.pricing-period {
  font-size: 14px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.45);
}

.pricing-tagline {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.4);
  margin-bottom: 24px;
}

.pricing-check {
  background: rgba(167, 139, 250, 0.18);
  color: #a78bfa;
}

.pricing-badges {
  display: flex;
  gap: 8px;
  margin-bottom: 4px;
}

.pricing-badge.trial {
  color: #34d399;
  background: rgba(52, 211, 153, 0.12);
  border-color: rgba(52, 211, 153, 0.28);
}

.pricing-check.star {
  background: rgba(250, 204, 21, 0.15);
  color: #facc15;
  font-size: 10px;
}

.pricing-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.08);
  margin: 4px 0;
}

.included-free span {
  color: rgba(255, 255, 255, 0.4);
  font-style: italic;
}

.pricing-note {
  margin-top: 14px;
  font-size: 12px;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.4);
  text-align: center;
}

.btn-plan:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none !important;
}

@media (max-width: 640px) {
  .pricing-grid {
    grid-template-columns: 1fr;
    max-width: 400px;
  }
}
</style>