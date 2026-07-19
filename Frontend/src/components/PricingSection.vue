<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const auth = useAuthStore();

const startMyBetaJourney = () => {
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

            <div class="pricing-card w-full max-w-md">
                <div class="pricing-name">🎉 Beta Access</div>
                <div class="pricing-price"><span class="pricing-amount">Free</span></div>
                <div class="pricing-tagline">You've been selected as one of our early beta users.</div>
                <div class="pricing-features">
                    <div class="pricing-feature">
                        <div class="pricing-check" style="background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.4)">✓</div>
                        <span>5 personalized chats every day</span>
                    </div>
                    <div class="pricing-feature">
                        <div class="pricing-check" style="background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.4)">✓</div>
                        <span>Complete onboarding & personality profile</span>
                    </div>
                    <div class="pricing-feature">
                        <div class="pricing-check" style="background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.4)">✓</div>
                        <span>Full conversation history</span>
                    </div>
                    <div class="pricing-feature">
                        <div class="pricing-check" style="background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.4)">✓</div>
                        <span>Help shape Future Self with your feedbacks</span>
                    </div>
                    <div class="pricing-feature">
                        <div class="pricing-check" style="background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.4)">✓</div>
                        <span>🎁 Lock in exclusive Founder Pricing when Premium launches</span>
                    </div>
                </div>
                <button class="btn-plan secondary" @click="startMyBetaJourney()" v-if="!auth.isAuthenticated">Start My Beta Journey</button>
                <button class="btn-plan secondary" @click="navigateToChat()" v-else>Start chatting</button>
            </div>

            <!-- <div class="pricing-card highlight">
                <span class="pricing-badge">Most Popular</span>
                <div class="pricing-name">Pathfinder</div>
                <div class="pricing-price"><span class="pricing-amount">$12</span><span class="pricing-period">/mo</span></div>
                <div class="pricing-tagline">For serious growth.</div>
                <div class="pricing-features">
                <div class="pricing-feature">
                    <div class="pricing-check" style="background:rgba(167,139,250,0.2);color:#a78bfa">✓</div>
                    <span>Unlimited chats</span>
                </div>
                <div class="pricing-feature">
                    <div class="pricing-check" style="background:rgba(167,139,250,0.2);color:#a78bfa">✓</div>
                    <span>Full onboarding profile</span>
                </div>
                <div class="pricing-feature">
                    <div class="pricing-check" style="background:rgba(167,139,250,0.2);color:#a78bfa">✓</div>
                    <span>All 4 tone modes</span>
                </div>
                <div class="pricing-feature">
                    <div class="pricing-check" style="background:rgba(167,139,250,0.2);color:#a78bfa">✓</div>
                    <span>Voice input</span>
                </div>
                <div class="pricing-feature">
                    <div class="pricing-check" style="background:rgba(167,139,250,0.2);color:#a78bfa">✓</div>
                    <span>Weekly reflections</span>
                </div>
                <div class="pricing-feature">
                    <div class="pricing-check" style="background:rgba(167,139,250,0.2);color:#a78bfa">✓</div>
                    <span>Role model wisdom</span>
                </div>
                </div>
                <button class="btn-plan primary">Start 7-Day Free Trial</button>
            </div>

            <div class="pricing-card">
                <div class="pricing-name">Visionary</div>
                <div class="pricing-price"><span class="pricing-amount">$29</span><span class="pricing-period">/mo</span></div>
                <div class="pricing-tagline">The complete transformation.</div>
                <div class="pricing-features">
                <div class="pricing-feature">
                    <div class="pricing-check" style="background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.4)">✓</div>
                    <span>Everything in Pathfinder</span>
                </div>
                <div class="pricing-feature">
                    <div class="pricing-check" style="background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.4)">✓</div>
                    <span>Multiple Future Self profiles</span>
                </div>
                <div class="pricing-feature">
                    <div class="pricing-check" style="background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.4)">✓</div>
                    <span>Goal milestone tracking</span>
                </div>
                <div class="pricing-feature">
                    <div class="pricing-check" style="background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.4)">✓</div>
                    <span>Priority support</span>
                </div>
                <div class="pricing-feature">
                    <div class="pricing-check" style="background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.4)">✓</div>
                    <span>Early feature access</span>
                </div>
                </div>
                <button class="btn-plan secondary">Go Visionary</button>
            </div> -->

            </div>
        </div>
        </section>
</template>

<style scoped></style>