import axios from 'axios'
import { isDevToolsActive } from '@/utils/securityShield'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
})

api.interceptors.request.use((config) => {
    // If DevTools is active, kill all outgoing network traffic immediately
    if (isDevToolsActive()) {
        const error = new Error('Network request blocked: Developer tools are restricted.')
        error.isDevToolsBlocked = true
        return Promise.reject(error)
    }

    const token = localStorage.getItem('token')

    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }

    return config
})

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 503) {
            import('@/router').then(({ default: router }) => {
                if (router.currentRoute.value.name !== 'Maintenance') {
                    router.push({ name: 'Maintenance' })
                }
            })
        }
        return Promise.reject(error)
    }
)

export default api
