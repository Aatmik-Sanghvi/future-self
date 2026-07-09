import { createApp } from 'vue'
import { createPinia } from 'pinia'
import Vue3Toastify from 'vue3-toastify'
import App from './App.vue'
import router from './router'
import './assets/styles/auth.css'
import './assets/styles/main.css'
import './assets/styles/chat.css'
import 'vue3-toastify/dist/index.css'

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.use(Vue3Toastify, {
  autoClose: 1000,
  theme: 'dark',
  newestOnTop: true,
  closeOnClick: true,
  pauseOnHover: true,
  draggable: true,
})
app.mount('#app')
