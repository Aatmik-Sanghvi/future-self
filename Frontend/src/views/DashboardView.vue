<script setup>
  import Navbar from '@/components/Navbar.vue';
  import HeroSection from '@/components/HeroSection.vue';
  import StatsBar from '@/components/StatsBar.vue';
  import HowItWorks from '@/components/HowItWorks.vue';
  import Testimonials from '@/components/Testimonials.vue';
  import PricingSection from '@/components/PricingSection.vue';
  import CTASection from '@/components/CTASection.vue';
  import FeaturesSection from '@/components/FeaturesSection.vue';

  import { useRouter } from 'vue-router';

  const router = useRouter();

  const register = () => {
      router.push({name: 'Register'});
  }
</script>

<template>
  <div>
    <Navbar />
    <HeroSection :register="register" />
    <StatsBar />
    <FeaturesSection />
    <HowItWorks />
    <Testimonials />
    <PricingSection />
    <CTASection :register="register" />
  </div>
</template>
