import api from '@/api/axios'

class GeneralService {
    getStats() {
        return api.get('/stats')
    }

    postMood(mood) {
        return api.post('/daily-mood-checkin', mood)
    }
}

export default new GeneralService()