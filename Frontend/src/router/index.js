import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    redirect: ''
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { title: 'Sign In - FutureYou' }
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: { title: 'Create Account - FutureYou' }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: () => import('@/views/auth/ForgotPasswordView.vue'),
    meta: { title: 'Forgot Password - FutureYou' }
  },
  {
    path: '/',
    name: '',
    component: () => import('@/views/DashboardView.vue'),
    meta: { title: 'Future You - Home' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to) => {
  document.title = to.meta.title || 'FutureYou'
})

export default router
