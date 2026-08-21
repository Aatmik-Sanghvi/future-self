<script setup>
  import Navbar from '@/components/Navbar.vue';
  import HeroSection from '@/components/HeroSection.vue';
  import StatsBar from '@/components/StatsBar.vue';
  import HowItWorks from '@/components/HowItWorks.vue';
  import Testimonials from '@/components/Testimonials.vue';
  import PricingSection from '@/components/PricingSection.vue';
  import FAQSection from '@/components/FAQSection.vue';
  import CTASection from '@/components/CTASection.vue';
  import FeaturesSection from '@/components/FeaturesSection.vue';
  import FooterSection from '@/components/FooterSection.vue';
  import MoodPopup from '@/components/MoodPopup.vue';
  import { useAuthStore } from '@/stores/auth';

  import { ref, computed } from 'vue'
  import { useRouter } from 'vue-router';

  const router = useRouter();

  const register = () => {
      router.push({name: 'Register'});
  }

  const users = ref(0)

  const auth = useAuthStore()
  // Mood popup state
  const showMoodPopup = computed(() => auth.isAuthenticated && !auth.isDailyMoodCheckIn)
  const moodDismissed = ref(false)
  

  function onMoodClosed() {
    moodDismissed.value = true
  }
  
</script>

<template>
  <div>
    <MoodPopup
      v-if="showMoodPopup && !moodDismissed"
      @close="onMoodClosed"
    />
    <Navbar />
    <HeroSection :register="register" :users="users"/>
    <StatsBar v-model="users"/>
    <FeaturesSection />
    <HowItWorks />
    <Testimonials />
    <PricingSection />
    <FAQSection />
    <CTASection :register="register" />
    <FooterSection />
  </div>
</template>
