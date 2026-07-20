import api from '@/api/axios'

class AuthService {

    login(credentials) {
        return api.post('/login', credentials)
    }

    register(userData) {
        return api.post('/register', userData)
    }

    getGoogleAuthRedirectUrl() {
        return api.get('/auth/google/redirect')
    }

    redirectToGoogle() {
        const baseURL = api.defaults.baseURL || '/api/V1/'
        const target = baseURL.startsWith('http') ? `${baseURL}auth/google/redirect` : `${window.location.origin}${baseURL.startsWith('/') ? '' : '/'}${baseURL}auth/google/redirect`
        window.location.href = target
    }

    forgotPassword(email) {
        return api.post('/forgot-password', email)
    }

    verifyOtp(data) {
        return api.post('/verify-otp', data)
    }

    resetPassword(data) {
        return api.post('/reset-password', data)
    }

    profile() {
        return api.get('/profile')
    }

    logout() {
        return api.post('/logout')
    }

    updateProfile(data) {
        return api.post('/update-profile', data, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
    }

    updatePassword(data) {
        return api.post('/update-password', data)
    }

    deleteAccount() {
        return api.post('/delete-account')
    }
}

export default new AuthService()