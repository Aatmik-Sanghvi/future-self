import api from './axios'

export const login = (creadentials) => {
    return api.post('/login', creadentials)
}