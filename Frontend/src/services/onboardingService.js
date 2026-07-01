import api from '@/api/axios'

class OnboardingService {

    getDetail(data) {
        return api.post('/onboarding/get-detail', data)
    }

    saveGoals(goals) {
        return api.post('/onboarding/goals', goals)
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
}

export default new OnboardingService()
