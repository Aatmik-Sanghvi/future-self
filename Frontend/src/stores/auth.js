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

    async function register(userData) {
        loading.value = true
        error.value = null

        try {
            const response = await AuthService.register(userData)
            console.log('Register response:', response.data);
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
        login,
        register,
        fetchUser,
        checkAuth,
        clearAuth,
        logout,
        toastMessage
    }
})