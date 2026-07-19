<script setup>
import { ref, computed, nextTick, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import ChatService from '@/services/chatService'
import feedbackService from '@/services/feedbackService'
import FeedbackModal from '@/components/FeedbackModal.vue'
import { marked } from 'marked'
import { useRouter } from 'vue-router'

// Configure marked for clean rendering
marked.setOptions({
  breaks: true,
  gfm: true,
})

function renderMarkdown(content) {
  if (!content) return ''
  return marked(content.trim())
}

const auth = useAuthStore()
const router = useRouter()

// State
const conversations = ref([])
const activeConversationId = ref(null)
const messages = ref([])
const inputMessage = ref('')
const isLoading = ref(false)
const isTyping = ref(false)
const sidebarOpen = ref(false)
const loadingConversations = ref(false)
const showDeleteModal = ref(false)
const conversationToDelete = ref(null)
const userDailyMessageCount = ref();
const canMessage = ref();
const dailyMessageLimit = ref();

// Feedback state
const showFeedbackModal = ref(false)
const hasFeedbackSubmitted = ref(false)
const hasFeedbackSkipped = ref(false)
const existingFeedback = ref(null)

// Refs
const messagesContainer = ref(null)
const textareaRef = ref(null)

// Computed
const userInitials = computed(() => {
  if (!auth.user?.name) return '?'
  return auth.user.name
    .split(' ')
    .map(w => w[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const canSend = computed(() => inputMessage.value.trim().length > 0 && !isLoading.value && canMessage.value)

const hasMessages = computed(() => messages.value.length > 0)

async function checkFeedbackStatus() {
  try {
    const res = await feedbackService.getFeedbackStatus()
    hasFeedbackSubmitted.value = res.data.data.hasSubmitted
    hasFeedbackSkipped.value = res.data.data.hasSkipped
    existingFeedback.value = res.data.data.feedback || null
  } catch (err) {
    console.error('Failed to check feedback status', err)
  }
}

function onFeedbackSubmitted(savedData) {
  hasFeedbackSubmitted.value = true
  if (savedData) {
    existingFeedback.value = savedData
  }
  auth.toastMessage('Feedback saved successfully. Thank you! ❤️', { type: 'success' })
}

function onFeedbackSkipped() {
  hasFeedbackSkipped.value = true
}

// Methods
async function loadConversations() {
  loadingConversations.value = true
  try {
    const res = await ChatService.getConversations()
    const data = res.data.data
    conversations.value = data.conversations || []
    canMessage.value = data.canMessage
    dailyMessageLimit.value = data.dailyMessageLimit
    userDailyMessageCount.value = data.userMessageCount

    if (canMessage.value === false) {
      checkFeedbackStatus()
    }
  } catch (err) {
    console.error('Failed to load conversations', err)
  } finally {
    loadingConversations.value = false
  }
}

async function selectConversation(convo) {
  if (activeConversationId.value === convo.id) return
  activeConversationId.value = convo.id
  messages.value = []
  sidebarOpen.value = false

  try {
    const res = await ChatService.getMessages(convo.id)
    messages.value = (res.data.data.messages || []).map(m => ({
      id: m.id,
      role: m.role,
      content: m.content,
      time: formatTime(m.created_at)
    }))
    userDailyMessageCount.value = res.data.data.userMessageCount
    canMessage.value = res.data.data.canMessage
    dailyMessageLimit.value = res.data.data.dailyMessageLimit

    if (canMessage.value === false) {
      checkFeedbackStatus()
    }

    await nextTick()
    scrollToBottom()
  } catch (err) {
    console.error('Failed to load messages', err)
  }
}

function startNewChat() {
  activeConversationId.value = null
  messages.value = []
  inputMessage.value = ''
  sidebarOpen.value = false
  nextTick(() => textareaRef.value?.focus())
}

function confirmDeleteConversation(convo, event) {
  event.stopPropagation()
  conversationToDelete.value = convo
  showDeleteModal.value = true
}

function cancelDelete() {
  showDeleteModal.value = false
  conversationToDelete.value = null
}

async function deleteConversation() {
  if (!conversationToDelete.value) return
  const id = conversationToDelete.value.id

  try {
    // TODO: await ChatService.deleteConversation(id)
    const response = await ChatService.deleteConversation(id)
    conversations.value = conversations.value.filter(c => c.id !== id)
    if (activeConversationId.value === id) {
      activeConversationId.value = null
      messages.value = []
    }
    auth.toastMessage(response.data.message, {type:'success'})
  } catch (err) {
    console.error('Failed to delete conversation', err)
    auth.toastMessage(err.response.data.message, {type:'error'})
  } finally {
    showDeleteModal.value = false
    conversationToDelete.value = null
  }
}

async function sendMessage() {
  const text = inputMessage.value.trim()
  if (!text || isLoading.value) return

  // Add user message immediately
  const userMsg = {
    id: Date.now(),
    role: 'user',
    content: text,
    time: formatTime(new Date().toISOString())
  }
  messages.value.push(userMsg)
  inputMessage.value = ''
  resetTextareaHeight()

  isLoading.value = true
  isTyping.value = true
  await nextTick()
  scrollToBottom()

  try {
    const res = await ChatService.sendMessage({
      message: text,
      conversation_id: activeConversationId.value
    })

    const data = res.data.data
    isTyping.value = false

    // Update message limit state
    if (data.canMessage !== undefined) {
      canMessage.value = data.canMessage
      dailyMessageLimit.value = data.dailyMessageLimit
      userDailyMessageCount.value = data.userMessageCount
    }

    // Set the conversation ID if it's a new conversation
    if (!activeConversationId.value && data.conversation_id) {
      activeConversationId.value = data.conversation_id
      // Reload conversation list to include the new one
      loadConversations()
    }

    // Add AI response
    const aiMsg = {
      id: Date.now() + 1,
      role: 'assistant',
      content: data.reply,
      time: formatTime(new Date().toISOString())
    }
    messages.value.push(aiMsg)

    await nextTick()
    scrollToBottom()
  } catch (err) {
    isTyping.value = false
    console.error('Failed to send message', err)
    
    // Check if daily message limit was reached
    if (err.response && err.response.status === 429) {
      canMessage.value = false
      checkFeedbackStatus()
      if (err.response.data && err.response.data.message) {
        auth.toastMessage(err.response.data.message, { type: 'warning' })
      }
      messages.value.push({
        id: Date.now() + 2,
        role: 'assistant',
        content: '🔒 You have reached your daily message limit. Please come back tomorrow to continue our conversation.',
        time: formatTime(new Date().toISOString())
      })
    } else {
      // Add general error message
      messages.value.push({
        id: Date.now() + 2,
        role: 'assistant',
        content: 'Sorry, I couldn\'t process your message. Please try again.',
        time: formatTime(new Date().toISOString())
      })
    }
    await nextTick()
    scrollToBottom()
  } finally {
    isLoading.value = false
  }
}

function handleKeydown(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault()
    sendMessage()
  }
}

function handleQuickPrompt(text) {
  inputMessage.value = text
  nextTick(() => sendMessage())
}

function autoResizeTextarea() {
  const el = textareaRef.value
  if (!el) return
  el.style.height = 'auto'
  el.style.height = Math.min(el.scrollHeight, 120) + 'px'
}

function resetTextareaHeight() {
  nextTick(() => {
    if (textareaRef.value) {
      textareaRef.value.style.height = 'auto'
    }
  })
}

function scrollToBottom() {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

function parseDate(dateStr) {
  if (!dateStr) return new Date()
  if (dateStr instanceof Date) return dateStr
  
  // If the date string is a raw database timestamp (e.g., "2026-07-18 09:28:08")
  // without explicit timezone info, assume it is UTC.
  if (typeof dateStr === 'string' && !dateStr.includes('T') && !dateStr.includes('Z') && !dateStr.includes('+')) {
    return new Date(dateStr.replace(' ', 'T') + 'Z')
  }
  return new Date(dateStr)
}

function formatTime(dateStr) {
  if (!dateStr) return ''
  const d = parseDate(dateStr)
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

function formatRelativeTime(dateStr) {
  if (!dateStr) return ''
  const now = new Date()
  const d = parseDate(dateStr)
  const diff = Math.floor((now - d) / 1000)

  if (diff < 60) return 'Just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`
  return d.toLocaleDateString([], { month: 'short', day: 'numeric' })
}

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

const goBack = () => {
  router.push({name: 'Dashboard'});
}

// Quick prompts
const quickPrompts = [
  'Motivate me',
  'I feel lost',
  'Calm my anxiety',
  'What should I focus on?',
  'Help me set goals',
  'I need confidence'
]

// Load conversations on mount
onMounted(() => {
  loadConversations()
})
</script>

<template>
  <div class="chat-layout">
    <!-- Optional Feedback Modal -->
    <FeedbackModal
      :show="showFeedbackModal"
      :initialData="existingFeedback"
      @close="showFeedbackModal = false"
      @submitted="onFeedbackSubmitted"
      @skipped="onFeedbackSkipped"
    />
    <!-- Mobile sidebar overlay -->
    <div
      class="sidebar-overlay"
      :class="{ show: sidebarOpen }"
      @click="sidebarOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside class="chat-sidebar" :class="{ open: sidebarOpen }">
      <div class="sidebar-header">
        <router-link to="/" class="sidebar-logo">
          <div class="sidebar-logo-icon">✨</div>
          <span class="sidebar-logo-name">FutureYou</span>
        </router-link>
        <button class="btn-new-chat" @click="startNewChat" id="btn-new-chat">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" />
          </svg>
          New Chat
        </button>
      </div>

      <div class="sidebar-conversations">
        <div v-if="conversations.length > 0">
          <div class="sidebar-section-label">Conversations</div>
          <div
            v-for="convo in conversations"
            :key="convo.id"
            class="conversation-item"
            :class="{ active: activeConversationId === convo.id }"
            @click="selectConversation(convo)"
          >
            <div class="conversation-icon">💬</div>
            <div class="conversation-info">
              <div class="conversation-title">{{ convo.title || 'New Conversation' }}</div>
              <div class="conversation-time">{{ formatRelativeTime(convo.updated_at) }}</div>
            </div>
            <button
              class="btn-delete-convo"
              @click="confirmDeleteConversation(convo, $event)"
              title="Delete conversation"
              :id="'btn-delete-convo-' + convo.id"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6" /><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" /><path d="M10 11v6" /><path d="M14 11v6" /><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
              </svg>
            </button>
          </div>
        </div>
        <div v-else-if="!loadingConversations" class="sidebar-empty">
          <div class="sidebar-empty-icon">💭</div>
          <div class="sidebar-empty-text">No conversations yet.<br />Start chatting with your future self!</div>
        </div>
      </div>
    </aside>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="showDeleteModal" class="delete-modal-overlay" @click.self="cancelDelete">
          <div class="delete-modal">
            <div class="delete-modal-icon">🗑️</div>
            <h3 class="delete-modal-title">Delete Conversation</h3>
            <p class="delete-modal-text">
              Are you sure you want to delete
              <strong>"{{ conversationToDelete?.title || 'this conversation' }}"</strong>?
              This action cannot be undone.
            </p>
            <div class="delete-modal-actions">
              <button class="btn-modal-cancel" @click="cancelDelete" id="btn-modal-cancel">Cancel</button>
              <button class="btn-modal-delete" @click="deleteConversation" id="btn-modal-delete">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="3 6 5 6 21 6" /><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                </svg>
                Delete
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Main Chat -->
    <div class="chat-main">
      <!-- Chat Header -->
      <div class="chat-main-header">
        <div class="chat-main-header-left">
          <button class="btn-sidebar-toggle" @click="toggleSidebar" id="btn-sidebar-toggle">
            ☰
          </button>
          <div class="chat-main-avatar">✨</div>
          <div>
            <div class="chat-main-name">Future You</div>
            <div class="chat-main-status">
              <div class="chat-main-status-dot"></div>
              <!-- <span class="chat-main-status-text">5 years ahead of you</span> -->
            </div>
          </div>
        </div>
        <div class="profile-nav-actions" v-if="auth.isOnboarded">
          <button class="profile-nav-btn" @click="goBack()" id="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="19" y1="12" x2="5" y2="12" /><polyline points="12 19 5 12 12 5" />
            </svg>
            Back
          </button>
        </div>
      </div>

      <!-- Messages or Welcome -->
      <div v-if="hasMessages" class="chat-messages-area" ref="messagesContainer">
        <div
          v-for="msg in messages"
          :key="msg.id"
          class="message-row"
          :class="msg.role"
        >
          <div v-if="msg.role === 'assistant'" class="msg-avatar ai">✨</div>
          <div v-else class="msg-avatar user-av">
            <img v-if="auth.user.profile_image != null" :src="'/storage/' + auth.user.profile_image" alt="profile-image" class="profile-image" />
            <span v-else>{{ userInitials }}</span>
          </div>
          <div class="msg-content">
            <div
              class="msg-bubble"
              :class="msg.role === 'assistant' ? 'ai-bubble' : 'user-bubble'"
              v-html="renderMarkdown(msg.content)"></div>
            <div class="msg-time">{{ msg.time }}</div>
          </div>
        </div>

        <!-- Typing indicator -->
        <div v-if="isTyping" class="typing-indicator">
          <div class="msg-avatar ai">✨</div>
          <div class="typing-bubble">
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
          </div>
        </div>
      </div>

      <div v-else class="chat-welcome">
        <div class="welcome-icon">✨</div>
        <h1 class="welcome-title">Talk to Your Future Self</h1>
        <p class="welcome-sub">
          Ask anything — about your goals, fears, or the road ahead. Your future self is here to guide you.
        </p>
        <div class="quick-prompts" v-if="canMessage !== false">
          <button
            v-for="prompt in quickPrompts"
            :key="prompt"
            class="quick-prompt-chip"
            @click="handleQuickPrompt(prompt)"
          >
            {{ prompt }}
          </button>
        </div>
        <!-- Daily limit banner inside welcome -->
        <div v-if="canMessage === false" class="message-limit-banner">
          <div class="limit-banner-icon">🔒</div>
          <div class="limit-banner-content">
            <div class="limit-banner-title">Daily limit reached</div>
            <div class="limit-banner-text">
              You've used all <strong>{{ dailyMessageLimit }}</strong> messages for today. Come back tomorrow to continue your conversation.
            </div>
            <button class="btn-give-feedback" @click="showFeedbackModal = true" id="btn-give-feedback-welcome">
              <span>{{ hasFeedbackSubmitted ? '✏️' : '💬' }}</span> {{ hasFeedbackSubmitted ? 'Edit Your Feedback' : 'Give Feedback (Optional)' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Limit banner below messages area -->
      <div v-if="hasMessages && canMessage === false" class="chat-input-bar">
        <div class="message-limit-banner">
          <div class="limit-banner-icon">🔒</div>
          <div class="limit-banner-content">
            <div class="limit-banner-title">Daily limit reached</div>
            <div class="limit-banner-text">
              You've used all <strong>{{ dailyMessageLimit }}</strong> messages for today. Come back tomorrow to continue your conversation.
            </div>
            <button class="btn-give-feedback" @click="showFeedbackModal = true" id="btn-give-feedback-messages">
              <span>{{ hasFeedbackSubmitted ? '✏️' : '💬' }}</span> {{ hasFeedbackSubmitted ? 'Edit Your Feedback' : 'Give Feedback (Optional)' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Input Bar -->
      <div v-if="canMessage !== false" class="chat-input-bar">
        <div class="chat-input-wrapper">
          <textarea
            ref="textareaRef"
            v-model="inputMessage"
            class="chat-textarea"
            placeholder="Ask your future self anything..."
            rows="1"
            @keydown="handleKeydown"
            @input="autoResizeTextarea"
            id="chat-input"
          ></textarea>
          <button
            class="btn-send"
            :disabled="!canSend"
            @click="sendMessage"
            id="btn-send"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="22" y1="2" x2="11" y2="13" /><polygon points="22 2 15 22 11 13 2 9 22 2" />
            </svg>
          </button>
        </div>
        <div class="chat-input-hint">Press Enter to send · Shift + Enter for new line</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* All styles are in chat.css */
</style>
