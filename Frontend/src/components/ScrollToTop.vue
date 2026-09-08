<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const isVisible = ref(false)
const bottomOffset = ref(28)

const handleScroll = () => {
  // Hide on chat interface to avoid overlapping input controls
  if (route.name === 'Chat') {
    isVisible.value = false
    return
  }

  // Show when user has scrolled down more than 350px
  isVisible.value = window.scrollY > 350

  // Calculate position relative to footer so the button dynamically stays above the footer
  const footer = document.querySelector('footer')
  const isMobile = window.innerWidth <= 640
  const defaultBottom = isMobile ? 20 : 28
  const clearance = isMobile ? 12 : 16

  if (footer) {
    const footerRect = footer.getBoundingClientRect()
    const windowHeight = window.innerHeight
    const overlap = windowHeight - footerRect.top

    if (overlap > 0) {
      bottomOffset.value = overlap + clearance
    } else {
      bottomOffset.value = defaultBottom
    }
  } else {
    bottomOffset.value = defaultBottom
  }
}

const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  })
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true })
  window.addEventListener('resize', handleScroll, { passive: true })
  handleScroll()
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('resize', handleScroll)
})
</script>

<template>
  <Transition name="scroll-top-fade">
    <button
      v-if="isVisible"
      type="button"
      class="scroll-to-top-btn"
      :style="{ bottom: `${bottomOffset}px` }"
      @click="scrollToTop"
      aria-label="Scroll to top"
      title="Scroll to top"
    >
      <svg
        class="scroll-top-icon"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2.5"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <polyline points="18 15 12 9 6 15"></polyline>
      </svg>
    </button>
  </Transition>
</template>

<style scoped>
.scroll-to-top-btn {
  position: fixed;
  right: 28px;
  z-index: 90;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.65);
  border: 1px solid rgba(255, 255, 255, 0.12);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  cursor: pointer;
  transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
  outline: none;
}

.scroll-to-top-btn:hover {
  transform: translateY(-3px) scale(1.06);
  background: rgba(124, 58, 237, 0.28);
  color: #ffffff;
  border-color: rgba(167, 139, 250, 0.55);
  box-shadow: 0 8px 24px rgba(124, 58, 237, 0.3), 0 0 20px rgba(167, 139, 250, 0.25);
}

.scroll-to-top-btn:active {
  transform: translateY(0) scale(0.96);
  background: rgba(124, 58, 237, 0.38);
}

.scroll-top-icon {
  width: 20px;
  height: 20px;
  transition: transform 0.2s ease;
}

.scroll-to-top-btn:hover .scroll-top-icon {
  transform: translateY(-2px);
}

/* Vue Transition */
.scroll-top-fade-enter-active,
.scroll-top-fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.scroll-top-fade-enter-from,
.scroll-top-fade-leave-to {
  opacity: 0;
  transform: translateY(12px) scale(0.85);
}

@media (max-width: 640px) {
  .scroll-to-top-btn {
    right: 20px;
    width: 42px;
    height: 42px;
  }

  .scroll-top-icon {
    width: 18px;
    height: 18px;
  }
}
</style>
