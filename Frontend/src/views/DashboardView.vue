<script setup>
  import Navbar from '@/components/Navbar.vue';
  import HeroSection from '@/components/HeroSection.vue';
  import StatsBar from '@/components/StatsBar.vue';
  import HowItWorks from '@/components/HowItWorks.vue';
  import Testimonials from '@/components/Testimonials.vue';
  import PricingSection from '@/components/PricingSection.vue';
  import CTASection from '@/components/CTASection.vue';
  import FeaturesSection from '@/components/FeaturesSection.vue';
</script>

<template>
  <div>
    <Navbar />
    <HeroSection />
    <StatsBar />
    <FeaturesSection />
    <HowItWorks />
    <Testimonials />
    <PricingSection />
    <CTASection />
  </div>
</template>
