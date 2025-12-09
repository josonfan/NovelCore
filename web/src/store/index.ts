import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({ token: '', context: {} as any }),
  actions: {
    setToken(token: string) {
      this.token = token
      localStorage.setItem('token', token)
    },
    setContext(ctx: any){
      this.context = ctx || {}
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
