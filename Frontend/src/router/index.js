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
      title: 'Sign In - FutureYou',
      guestOnly: true, 
    }
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: { 
      title: 'Create Account - FutureYou',
      guestOnly: true,
    }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: () => import('@/views/auth/ForgotPasswordView.vue'),
    meta: { title: 'Forgot Password - FutureYou' }
  },
  {
    path: '/logout',
    name: 'Logout',
    meta: { 
      title: 'Logout - FutureYou',
      guestOnly: true,
    }
  },
  {
    path: '/onboarding',
    name: 'Onboarding',
    component: () => import('@/views/OnboardingView.vue'),
    meta: { 
      title: 'Onboarding - FutureYou',
      requiresAuth: true,
    }
  },
  {
    path: '/chat',
    name: 'Chat',
    component: () => import('@/views/ChatView.vue'),
    meta: {
      title: 'Chat - FutureYou',
      requiresAuth: true,
    }
  },
  {
    path: '/',
    name: 'Dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: { 
      title: 'Future You - Home' ,
      redirectTo: true,
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to) => {
  document.title = to.meta.title || 'FutureYou'

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
