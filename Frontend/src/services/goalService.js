import api from '@/api/axios'

class GoalService {
  /**
   * Get comprehensive Goal Tracking overview for active or specified goal
   */
  getTracking(goalId = null) {
    const params = goalId ? { goal_id: goalId } : {}
    return api.get('/goals/tracking', { params })
  }

  /**
   * Get all goals belonging to the user with momentum summary
   */
  getAllGoals() {
    return api.get('/goals')
  }

  /**
   * Get specific goal details and tracking metrics
   */
  getGoal(id) {
    return api.get(`/goals/${id}`)
  }

  /**
   * Create a new goal
   */
  createGoal(payload) {
    return api.post('/goals', payload)
  }

  /**
   * Update goal details or status
   */
  updateGoal(id, payload) {
    return api.put(`/goals/${id}`, payload)
  }

  /**
   * Delete a goal
   */
  deleteGoal(id) {
    return api.delete(`/goals/${id}`)
  }

  /**
   * Update measurable goal target progress
   */
  updateProgress(id, payload) {
    return api.post(`/goals/${id}/progress`, payload)
  }
}

export default new GoalService()
