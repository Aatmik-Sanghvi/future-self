import api from './axios'

export const login = (creadentials) => {
    return api.post('/login', creadentials)
}

export const register = (data) => {
    return api.post('/register', data)
}