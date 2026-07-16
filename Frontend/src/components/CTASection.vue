<script setup>
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

defineProps({
    register: {
        type: Function,
        required: true
    }
})
</script>

<template>
    <section class="cta-section">
        <div class="cta-icon">✨</div>
        <h2 class="cta-h2">
            Your future self is already<br>
            <span class="grad-text">waiting.</span>
        </h2>
        <p class="cta-sub">The only question is — are you ready to listen?</p>
        <div v-if="!auth.isAuthenticated">
            <button class="btn-cta">
                <router-link to="/register" class="btn-cta-link">
                    Start Your Journey Free →
                </router-link>
            </button>
            <p class="cta-note">No credit card · Free forever plan available</p>
        </div>
    </section>
</template>

<style scoped></style>