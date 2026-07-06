import api from '@/api/axios'

class OnboardingService {

    getDetail(data) {
        return api.post('/onboarding/get-detail', data)
    }

    saveGoals(goals) {
        return api.post('/onboarding/goals', goals)
    }

    removeDetail(data) {
        return api.post('/onboarding/remove-detail', data)
    }

    saveFears(fear) {
        return api.post('/onboarding/fears', fear)
    }

    saveDesiredTraits(data) {
        return api.post('/onboarding/desired-traits', data)
    }

    saveRoleModels(data) {
        return api.post('/onboarding/role-models', data)
    }

    saveTone(data) {
        return api.post('/onboarding/tone', data)
    }

    updateOnboarded() {
        return api.get('/onboarding/onboarded')
    }
}

export default new OnboardingService()
