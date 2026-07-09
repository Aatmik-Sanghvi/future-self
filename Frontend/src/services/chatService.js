import api from '@/api/axios'

class ChatService {

    sendMessage(data) {
        return api.post('/chat', data)
    }

    getConversations() {
        return api.get('/conversations')
    }

    getMessages(conversationId) {
        return api.post('/messages', { conversation_id: conversationId })
    }

    deleteConversation(conversationId) {
        return api.post('/delete-conversation', { conversation_id: conversationId })
    }
}

export default new ChatService()
