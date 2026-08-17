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

  import { ref } from 'vue'
  import { useRouter } from 'vue-router';

  const router = useRouter();

  const register = () => {
      router.push({name: 'Register'});
  }

  const users = ref(0)
  
</script>

<template>
  <div>
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
