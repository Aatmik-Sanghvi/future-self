import axios from 'axios'

const api = axios.create({
    baseURL: '/api/V1/',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: true,
})

export default api