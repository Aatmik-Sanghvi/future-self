import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import AuthService from '@/services/authService'

export const useAuthStore = defineStore('auth', () => {

    const user = ref(null)

    const token = ref(localStorage.getItem('token'))

    const loading = ref(false)

    const error = ref(null)

    const isAuthenticated = computed(() => !!token.value)

    async function login(credentials) {
        loading.value = true
        error.value = null

        try{
            const response = await AuthService.login(credentials)
            console.log('Login response:', response.data);
            token.value = response.data.data.token
            localStorage.setItem('token', token.value)
            // await fetchUser()
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
            user.value = response.data
            return response.data
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

     function clearAuth() {
        user.value = null
        token.value = null
        localStorage.removeItem('token')
     }

    return {
        user,
        token,
        loading,
        error,
        isAuthenticated,
        login,
        fetchUser,
        checkAuth,
        clearAuth,
    }
})