<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const auth = useAuthStore();

const startMyJourney = () => {
  router.push({name: 'Login'});
}

const navigateToChat = () => {
  router.push({name: 'Chat'});
}
</script>

<template>
    <section class="pricing-section" id="pricing">
        <div class="container">
            <div class="pricing-header">
                <span class="section-label">Pricing</span>
                <h2 class="section-title">Invest in who you're becoming</h2>
                <p class="section-sub">Less than a coffee a week to talk to the best version of yourself.</p>
            </div>
            <div class="pricing-grid !flex justify-center">
                <div class="pricing-card highlight w-full max-w-md">
                    <div class="pricing-card-header">
                        <div class="pricing-name">Monthly Plan</div>
                        <span class="pricing-discount-badge">100% OFF</span>
                    </div>
                    <div class="pricing-price">
                        <span class="pricing-original" title="Rs. 199">₹199</span>
                        <span class="pricing-amount">Free</span>
                        <span class="pricing-period">/month</span>
                    </div>
                    <div class="pricing-features">
                        <div class="pricing-feature">
                            <div class="pricing-check">✓</div>
                            <span>Limited personalized chats every day</span>
                        </div>
                        <div class="pricing-feature">
                            <div class="pricing-check">✓</div>
                            <span>Goal tracking</span>
                        </div>
                        <div class="pricing-feature">
                            <div class="pricing-check">✓</div>
                            <span>Daily missions</span>
                        </div>
                        <div class="pricing-feature">
                            <div class="pricing-check">✓</div>
                            <span>Complete onboarding & personality profile</span>
                        </div>
                        <div class="pricing-feature">
                            <div class="pricing-check">✓</div>
                            <span>Full conversation history</span>
                        </div>
                        <div class="pricing-feature">
                            <div class="pricing-check">✓</div>
                            <span>Help shape Future Self with your feedback</span>
                        </div>
                    </div>
                    <button class="btn-plan primary" @click="startMyJourney()" v-if="!auth.isAuthenticated">Start My Journey</button>
                    <button class="btn-plan primary" @click="navigateToChat()" v-else>Start chatting</button>
                    <p class="pricing-note">🔒 No credit card or payment required to register</p>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.pricing-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.pricing-discount-badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.03em;
  color: #34d399;
  background: rgba(52, 211, 153, 0.12);
  border: 1px solid rgba(52, 211, 153, 0.28);
}

.pricing-price {
  display: flex;
  align-items: baseline;
  gap: 8px;
  margin-bottom: 20px;
}

.pricing-original {
  font-size: 22px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.45);
  text-decoration: line-through;
  text-decoration-thickness: 2px;
  text-decoration-color: #f43f5e;
}

.pricing-amount {
  font-size: 36px;
  font-weight: 900;
  color: #ffffff;
  line-height: 1;
}

.pricing-period {
  font-size: 14px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.45);
}

.pricing-check {
  background: rgba(167, 139, 250, 0.18);
  color: #a78bfa;
}

.pricing-note {
  margin-top: 14px;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.4);
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
}
</style>