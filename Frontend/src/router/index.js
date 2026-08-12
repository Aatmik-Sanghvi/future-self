import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const BASE_URL = 'https://futureself.in'
const DEFAULT_IMAGE = 'https://futureself.in/og-image.jpg'

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: { 
      title: 'FutureSelf — Meet the Future Version of Yourself with AI',
      description: 'Connect with the person you want to become. Set goals, overcome fears, and chat with your future self using AI. Start your self-transformation today.',
      keywords: 'AI future self, chat with future self, goal setting AI, future self visualization, personal growth AI coach, future self letter',
      canonical: `${BASE_URL}/`,
      ogTitle: 'FutureSelf — Meet the Future Version of Yourself with AI',
      ogDescription: 'Set goals, overcome fears, and chat with the future version of yourself using AI. 12,000+ users transforming their future.',
      ogImage: DEFAULT_IMAGE,
      redirectTo: true,
    }
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: {
      title: 'Sign In — FutureSelf',
      description: 'Log in to your FutureSelf account to continue chatting with your future self and tracking your goals.',
      keywords: 'FutureSelf login, sign in future self, AI goal tracker login',
      canonical: `${BASE_URL}/login`,
      ogTitle: 'Sign In — FutureSelf',
      ogDescription: 'Log in to access your personal AI future self coach and goal milestones.',
      ogImage: DEFAULT_IMAGE,
      guestOnly: true, 
    }
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: { 
      title: 'Create Account — FutureSelf | Start Free',
      description: 'Create your free FutureSelf account today. Define your goals, map your fears, and start conversing with your future self powered by AI.',
      keywords: 'FutureSelf register, sign up future self, free AI self-improvement app',
      canonical: `${BASE_URL}/register`,
      ogTitle: 'Create Account — FutureSelf',
      ogDescription: 'Start your journey. Talk to your future self and achieve your long-term goals.',
      ogImage: DEFAULT_IMAGE,
      guestOnly: true,
    }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: () => import('@/views/auth/ForgotPasswordView.vue'),
    meta: { 
      title: 'Forgot Password — FutureSelf',
      description: 'Reset your FutureSelf account password to regain access to your future self conversations.',
      canonical: `${BASE_URL}/forgot-password`,
      ogTitle: 'Reset Password — FutureSelf',
      ogDescription: 'Reset your password and reconnect with your future self.',
      ogImage: DEFAULT_IMAGE,
    }
  },
  {
    path: '/privacy-policy',
    name: 'PrivacyPolicy',
    component: () => import('@/views/PrivacyPolicyView.vue'),
    meta: { 
      title: 'Privacy Policy — FutureSelf',
      description: 'Read the FutureSelf Privacy Policy. We prioritize your data privacy, encryption, and goal security.',
      canonical: `${BASE_URL}/privacy-policy`,
      ogTitle: 'Privacy Policy — FutureSelf',
      ogDescription: 'Learn how FutureSelf protects your personal information and privacy.',
      ogImage: DEFAULT_IMAGE,
    }
  },
  {
    path: '/terms-of-service',
    name: 'TermsOfService',
    component: () => import('@/views/TermsOfServiceView.vue'),
    meta: { 
      title: 'Terms of Service — FutureSelf',
      description: 'Review the Terms of Service for using the FutureSelf AI platform and services.',
      canonical: `${BASE_URL}/terms-of-service`,
      ogTitle: 'Terms of Service — FutureSelf',
      ogDescription: 'Terms of Service for FutureSelf platform.',
      ogImage: DEFAULT_IMAGE,
    }
  },
  {
    path: '/auth/callback',
    name: 'SocialCallback',
    component: () => import('@/views/auth/SocialCallbackView.vue'),
    meta: { 
      title: 'Authenticating — FutureSelf',
      noIndex: true,
    }
  },
  {
    path: '/logout',
    name: 'Logout',
    meta: { 
      title: 'Logout — FutureSelf',
      guestOnly: true,
      noIndex: true,
    }
  },
  {
    path: '/onboarding',
    name: 'Onboarding',
    component: () => import('@/views/OnboardingView.vue'),
    meta: { 
      title: 'Setup Your Future Self — FutureSelf',
      description: 'Set up your future vision, goals, and personality traits.',
      requiresAuth: true,
      noIndex: true,
    }
  },
  {
    path: '/chat',
    name: 'Chat',
    component: () => import('@/views/ChatView.vue'),
    meta: {
      title: 'Chat with Your Future Self — FutureSelf',
      description: 'Interactive AI session with your future self.',
      requiresAuth: true,
      noIndex: true,
    }
  },
  {
    path: '/edit-profile',
    name: 'EditProfile',
    component: () => import('@/views/EditProfileView.vue'),
    meta: {
      title: 'Edit Profile & Goals — FutureSelf',
      requiresAuth: true,
      noIndex: true,
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else if (to.hash) {
      return { el: to.hash, behavior: 'smooth' }
    } else {
      return { top: 0 }
    }
  }
})

function updateMetaElement(selector, attribute, value) {
  let element = document.querySelector(selector)
  if (!element) {
    element = document.createElement(selector.startsWith('link') ? 'link' : 'meta')
    if (selector.includes('[name=')) {
      const nameMatch = selector.match(/\[name="?([^"\]]+)"?\]/)
      if (nameMatch) element.setAttribute('name', nameMatch[1])
    } else if (selector.includes('[property=')) {
      const propMatch = selector.match(/\[property="?([^"\]]+)"?\]/)
      if (propMatch) element.setAttribute('property', propMatch[1])
    } else if (selector.includes('[rel=')) {
      const relMatch = selector.match(/\[rel="?([^"\]]+)"?\]/)
      if (relMatch) element.setAttribute('rel', relMatch[1])
    }
    document.head.appendChild(element)
  }
  element.setAttribute(attribute, value)
}

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Restore the session after refresh
  if(auth.token && !auth.user) {
    await auth.checkAuth()
  }

  // protected pages - redirect to login if not authenticated
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { 
      name: 'Login' 
    }
  }

  // Guest pages - redirect authenticated users away from login/register
  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { 
      name: auth.isOnboarded ? 'Dashboard' : 'Onboarding' 
    }
  }

  // Redirect authenticated but non-onboarded users to onboarding from Dashboard
  if (to.meta.redirectTo && auth.isAuthenticated && !auth.isOnboarded) {
    return {
      name: 'Onboarding'
    }
  }

  return true
})

router.afterEach((to) => {
  // Update Title
  document.title = to.meta.title || 'FutureSelf — Meet the Future Version of Yourself'

  // Update Meta Description
  if (to.meta.description) {
    updateMetaElement('meta[name="description"]', 'content', to.meta.description)
  }

  // Update Meta Keywords
  if (to.meta.keywords) {
    updateMetaElement('meta[name="keywords"]', 'content', to.meta.keywords)
  }

  // Update Canonical URL
  const canonicalUrl = to.meta.canonical || `${BASE_URL}${to.path}`
  updateMetaElement('link[rel="canonical"]', 'href', canonicalUrl)

  // Update Robots Tag
  if (to.meta.noIndex) {
    updateMetaElement('meta[name="robots"]', 'content', 'noindex, nofollow')
  } else {
    updateMetaElement('meta[name="robots"]', 'content', 'index, follow')
  }

  // Update OpenGraph Meta Tags
  updateMetaElement('meta[property="og:title"]', 'content', to.meta.ogTitle || to.meta.title || 'FutureSelf')
  updateMetaElement('meta[property="og:description"]', 'content', to.meta.ogDescription || to.meta.description || '')
  updateMetaElement('meta[property="og:url"]', 'content', canonicalUrl)
  updateMetaElement('meta[property="og:image"]', 'content', to.meta.ogImage || DEFAULT_IMAGE)
  updateMetaElement('meta[property="og:type"]', 'content', 'website')

  // Update Twitter Card Meta Tags
  updateMetaElement('meta[name="twitter:card"]', 'content', 'summary_large_image')
  updateMetaElement('meta[name="twitter:title"]', 'content', to.meta.ogTitle || to.meta.title || 'FutureSelf')
  updateMetaElement('meta[name="twitter:description"]', 'content', to.meta.ogDescription || to.meta.description || '')
  updateMetaElement('meta[name="twitter:image"]', 'content', to.meta.ogImage || DEFAULT_IMAGE)
})

export default router
