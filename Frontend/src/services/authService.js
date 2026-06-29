import api from '@/api/axios'

class AuthService {

    login(credentials) {
        return api.post('/login', credentials)
    }

    register(userData) {
        return api.post('/register', userData)
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
}

export default new AuthService()