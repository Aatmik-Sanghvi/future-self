import api from '@/api/axios'

class ContactService {
  submitContact(data) {
    return api.post('/contact', data)
  }
}

export default new ContactService()
