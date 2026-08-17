import api from '@/api/axios'

class GeneralService {
    getStats() {
        return api.get('/stats')
    }
}

export default new GeneralService()