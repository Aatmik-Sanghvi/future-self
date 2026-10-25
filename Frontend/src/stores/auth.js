import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import AuthService from '@/services/authService'
import paymentService from '@/services/paymentService'
import { toast } from 'vue3-toastify'

export const useAuthStore = defineStore('auth', () => {

    const user = ref(null)

    const token = ref(localStorage.getItem('token'))

    const loading = ref(false)

    const error = ref(null)

    const isAuthenticated = computed(() => !!token.value)

    const isOnboarded = computed(() => user.value?.is_onboarded)

    const isDailyMoodCheckIn = computed(() => user.value?.is_daily_mood_check_in)

    const dailyStreak = computed(() => user.value?.daily_streak ?? 0)

    const isPro = computed(() => !!user.value?.is_pro)

    const proExpiresAt = computed(() => user.value?.pro_expires_at ?? null)

    const subscriptionStatus = computed(() => user.value?.subscription_status || (user.value?.is_pro ? 'active' : 'free'))

    const isTrial = computed(() => user.value?.subscription_status === 'trial')

    const trialEndsAt = computed(() => user.value?.trial_ends_at ?? null)

    const hasUsedTrial = computed(() => !!user.value?.has_used_trial)

    async function login(credentials) {
        loading.value = true
        error.value = null

        try {
            const response = await AuthService.login(credentials)
            console.log('Login response:', response.data);
            token.value = response.data.data.token
            localStorage.setItem('token', token.value)
            await fetchUser()
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message ?? 'Something went wrong.'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function registerSendOtp(userData) {
        loading.value = true
        error.value = null

        try {
            const response = await AuthService.registerSendOtp(userData)
            console.log('Register send OTP response:', response.data);
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message ?? 'Something went wrong.'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function registerVerifyOtp(data) {
        loading.value = true
        error.value = null

        try {
            const response = await AuthService.registerVerifyOtp(data)
            console.log('Register verify OTP response:', response.data);
            token.value = response.data.data.token
            localStorage.setItem('token', token.value)
            await fetchUser()
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message ?? 'Something went wrong.'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function registerResendOtp(data) {
        loading.value = true
        error.value = null

        try {
            const response = await AuthService.registerResendOtp(data)
            console.log('Register resend OTP response:', response.data);
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message ?? 'Something went wrong.'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function forgotPassword(email) {
        loading.value = true
        error.value = null

        try {
            const response = await AuthService.forgotPassword(email)
            console.log('Forgot password response:', response.data);
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message ?? 'Something went wrong.'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function verifyOtp(data) {
        loading.value = true
        error.value = null

        try {
            const response = await AuthService.verifyOtp(data)
            console.log('Verify OTP response:', response.data);
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message ?? 'Something went wrong.'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function resetPassword(data) {
        loading.value = true
        error.value = null

        try {
            const response = await AuthService.resetPassword(data)
            console.log('Reset password response:', response.data);
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message ?? 'Something went wrong.'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function fetchUser() {
        try {
            const response = await AuthService.profile()
            user.value = response.data.data
            return response.data.data
        } catch (error) {
            clearAuth()
            throw error
        }
    }

    async function checkAuth() {
        if (!token.value) {
            return false
        }

        try {
            await fetchUser()
            return true
        } catch (error) {
            clearAuth()
            return false
        }
    }

    async function logout() {
        const response = await AuthService.logout()
        clearAuth()
        return response.data
    }

    function clearAuth() {
        user.value = null
        token.value = null
        localStorage.removeItem('token')
    }

    function toastMessage(message, type = 'success') {
        toast(message, {
            "theme": "dark",
            "type": type,
            "autoClose": 3000,
        });
    }

    async function startTrial() {
        loading.value = true
        error.value = null
        try {
            const response = await paymentService.startTrial()
            user.value = response.data.data
            toastMessage(response.data.message || '7-day trial activated!', 'success')
            return response.data
        } catch (err) {
            const msg = err.response?.data?.message || 'Failed to activate trial.'
            toastMessage(msg, 'error')
            throw err
        } finally {
            loading.value = false
        }
    }

    async function cancelSubscription() {
        loading.value = true
        error.value = null
        try {
            const response = await paymentService.cancelSubscription()
            user.value = response.data.data
            toastMessage(response.data.message || 'Subscription cancelled.', 'info')
            return response.data
        } catch (err) {
            const msg = err.response?.data?.message || 'Failed to cancel subscription.'
            toastMessage(msg, 'error')
            throw err
        } finally {
            loading.value = false
        }
    }

    return {
        user,
        token,
        loading,
        error,
        isAuthenticated,
        isOnboarded,
        isDailyMoodCheckIn,
        dailyStreak,
        isPro,
        proExpiresAt,
        subscriptionStatus,
        isTrial,
        trialEndsAt,
        hasUsedTrial,
        login,
        registerSendOtp,
        registerVerifyOtp,
        registerResendOtp,
        forgotPassword,
        verifyOtp,
        resetPassword,
        fetchUser,
        checkAuth,
        clearAuth,
        logout,
        toastMessage,
        startTrial,
        cancelSubscription,
    }
})