import api from '@/api/axios'

class FeedbackService {
  submitFeedback(data) {
    return api.post('/feedback', data)
  }

  skipFeedback() {
    return api.post('/feedback', { is_skipped: true })
  }

  getFeedbackStatus() {
    return api.get('/feedback/status')
  }
}

export default new FeedbackService()
