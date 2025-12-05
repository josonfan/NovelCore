import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({ token: '' }),
  actions: {
    setToken(token: string) {
      this.token = token
      localStorage.setItem('token', token)
    },
    load() {
      this.token = localStorage.getItem('token') || ''
    },
    clear() {
      this.token = ''
      localStorage.removeItem('token')
    },
  },
})
