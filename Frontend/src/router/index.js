import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    redirect: ''
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: {
      title: 'Sign In - Future Self',
      guestOnly: true, 
    }
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: { 
      title: 'Create Account - Future Self',
      guestOnly: true,
    }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: () => import('@/views/auth/ForgotPasswordView.vue'),
    meta: { 
      title: 'Forgot Password - Future Self' 
    }
  },
  {
    path: '/privacy-policy',
    name: 'PrivacyPolicy',
    component: () => import('@/views/PrivacyPolicyView.vue'),
    meta: { 
      title: 'Privacy Policy - Future Self' 
    }
  },
  {
    path: '/terms-of-service',
    name: 'TermsOfService',
    component: () => import('@/views/TermsOfServiceView.vue'),
    meta: { 
      title: 'Terms of Service - Future Self' 
    }
  },
  {
    path: '/auth/callback',
    name: 'SocialCallback',
    component: () => import('@/views/auth/SocialCallbackView.vue'),
    meta: { 
      title: 'Authenticating - Future Self' 
    }
  },
  {
    path: '/logout',
    name: 'Logout',
    meta: { 
      title: 'Logout - Future Self',
      guestOnly: true,
    }
  },
  {
    path: '/onboarding',
    name: 'Onboarding',
    component: () => import('@/views/OnboardingView.vue'),
    meta: { 
      title: 'Onboarding - Future Self',
      requiresAuth: true,
    }
  },
  {
    path: '/chat',
    name: 'Chat',
    component: () => import('@/views/ChatView.vue'),
    meta: {
      title: 'Chat - Future Self',
      requiresAuth: true,
    }
  },
  {
    path: '/edit-profile',
    name: 'EditProfile',
    component: () => import('@/views/EditProfileView.vue'),
    meta: {
      title: 'Edit Profile - Future Self',
      requiresAuth: true,
    }
  },
  {
    path: '/',
    name: 'Dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: { 
      title: 'Home - Future Self' ,
      redirectTo: true,
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to) => {
  document.title = to.meta.title || 'Future Self'

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

  // Allow re-visiting onboarding for view/edit purposes

  // Redirect authenticated but non-onboarded users to onboarding from Dashboard
  if (to.meta.redirectTo && auth.isAuthenticated && !auth.isOnboarded) {
    return {
      name: 'Onboarding'
    }
  }

  return true
})

export default router
