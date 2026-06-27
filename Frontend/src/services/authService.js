import api from '@/api/axios'

class AuthService {

    login(credentials) {
        return api.post('/login', credentials)
    }

    register(userData) {
        return api.post('/register', userData)
    }

    profile() {
        return api.get('/profile')
    }

    logout() {
        return api.post('/logout')
    }
}

export default new AuthService()