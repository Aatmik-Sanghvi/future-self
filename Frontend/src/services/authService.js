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

    async redirectToGoogle() {
        try {
            const response = await this.getGoogleAuthRedirectUrl()
            const redirectUrl = response?.data?.data?.url || response?.data?.url
            if (redirectUrl) {
                window.location.href = redirectUrl
            } else {
                throw new Error('Google authorization URL was not returned by the server.')
            }
        } catch (error) {
            console.error('Google Auth Error:', error)
            throw error
        }
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