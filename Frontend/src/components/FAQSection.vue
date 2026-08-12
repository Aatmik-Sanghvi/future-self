<script setup>
import { ref } from 'vue'

const faqs = ref([
  {
    question: "What is FutureSelf and how does talking to your future self work?",
    answer: "FutureSelf is an AI-powered personal development platform. By combining your long-term ambitions, personal values, and current obstacles, our AI builds a personalized interactive model of your future self — 5 to 10 years ahead — who has already achieved those milestones and guides you with wisdom and accountability.",
    open: false
  },
  {
    question: "Is FutureSelf free to use?",
    answer: "Yes! FutureSelf offers a free tier that gives you immediate access to set up your future self identity, map your core goals, and engage in AI chat sessions. Premium upgrades are available for unlimited long-term conversations and role model personality channeling.",
    open: false
  },
  {
    question: "How does chatting with your future self accelerate goal achievement?",
    answer: "Visualizing and interacting with your future self leverages proven psychological techniques like prospective empathy and identity-based habits. When you talk with an entity representing your future success, you clarify your daily priorities, overcome short-term fears, and stay consistently motivated.",
    open: false
  },
  {
    question: "Is my personal goal data, journal, and chat history private?",
    answer: "100% private and secure. Your goals, fear maps, and personal chat history are stored with end-to-end encryption. We never sell your personal data or use your private conversations to train public models.",
    open: false
  },
  {
    question: "Can I customize the personality and tone of my Future Self?",
    answer: "Yes! You can configure your future self's voice and coaching style — whether you prefer empathetic and supportive, strict and disciplined, or modeled after inspirational figures like Marcus Aurelius, Kobe Bryant, or David Goggins.",
    open: false
  }
])

const toggleFaq = (index) => {
  faqs.value[index].open = !faqs.value[index].open
}
</script>

<template>
  <section class="faq-section" id="faq">
    <div class="container">
      <div class="faq-header">
        <span class="section-label">Got Questions?</span>
        <h2 class="section-title">
          Frequently Asked Questions<br />About FutureSelf
        </h2>
        <p class="section-sub">
          Everything you need to know about connecting with your future self.
        </p>
      </div>

      <div class="faq-grid">
        <div 
          v-for="(faq, index) in faqs" 
          :key="index"
          class="faq-card"
          :class="{ active: faq.open }"
          @click="toggleFaq(index)"
        >
          <div class="faq-question">
            <h3>{{ faq.question }}</h3>
            <span class="faq-icon">{{ faq.open ? '−' : '+' }}</span>
          </div>
          <div v-show="faq.open" class="faq-answer">
            <p>{{ faq.answer }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.faq-section {
  padding: 5rem 1.5rem;
  position: relative;
}

.container {
  max-width: 900px;
  margin: 0 auto;
}

.faq-header {
  text-align: center;
  margin-bottom: 3.5rem;
}

.section-label {
  display: inline-block;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: #a78bfa;
  margin-bottom: 0.75rem;
}

.section-title {
  font-size: 2.25rem;
  font-weight: 800;
  line-height: 1.25;
  color: #ffffff;
  margin-bottom: 1rem;
}

.section-sub {
  font-size: 1.1rem;
  color: #94a3b8;
}

.faq-grid {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.faq-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(12px);
}

.faq-card:hover {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(167, 139, 250, 0.3);
  transform: translateY(-2px);
}

.faq-card.active {
  background: linear-gradient(135deg, rgba(167, 139, 250, 0.08), rgba(255, 255, 255, 0.04));
  border-color: rgba(167, 139, 250, 0.4);
}

.faq-question {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.faq-question h3 {
  font-size: 1.15rem;
  font-weight: 600;
  color: #f8fafc;
  margin: 0;
  line-height: 1.4;
}

.faq-icon {
  font-size: 1.5rem;
  font-weight: 400;
  color: #a78bfa;
  min-width: 24px;
  text-align: center;
}

.faq-answer {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.faq-answer p {
  font-size: 1rem;
  line-height: 1.65;
  color: #cbd5e1;
  margin: 0;
}
</style>
