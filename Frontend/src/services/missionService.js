import api from '@/api/axios'

class MissionService {
  /**
   * Get today's daily mission and summary stats
   */
  getTodayMission() {
    return api.get('/daily-mission/today')
  }

  /**
   * Explicitly generate or regenerate today's mission
   */
  generateMission(payload = {}) {
    return api.post('/daily-mission/generate', payload)
  }

  /**
   * Mark a mission as completed after honesty confirmation
   */
  completeMission(missionId, payload) {
    return api.post(`/daily-mission/${missionId}/complete`, payload)
  }

  /**
   * Get paginated mission history with filters and stats
   */
  getHistory(params = {}) {
    return api.get('/daily-mission/history', { params })
  }

  /**
   * Get user's mission email and reminder preferences
   */
  getReminderSettings() {
    return api.get('/daily-mission/reminder-settings')
  }

  /**
   * Update user's mission email and reminder preferences
   */
  updateReminderSettings(payload) {
    return api.post('/daily-mission/reminder-settings', payload)
  }
}

export default new MissionService()
