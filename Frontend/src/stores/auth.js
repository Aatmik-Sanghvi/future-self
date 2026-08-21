import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import AuthService from '@/services/authService'
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

    async function login(credentials) {
        loading.value = true
        error.value = null

        try{
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
        } catch(err) {
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
        } catch(err) {
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
        } catch(err) {
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
        if(!token.value) {
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

    return {
        user,
        token,
        loading,
        error,
        isAuthenticated,
        isOnboarded,
        isDailyMoodCheckIn,
        dailyStreak,
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
        toastMessage
    }
})