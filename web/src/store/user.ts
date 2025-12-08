import { defineStore } from 'pinia'
import { fetchProfile, updateProfile } from '../api/auth'

export const useUserStore = defineStore('user', {
  state: () => ({ profile: null as any }),
  actions: {
    async load() {
      this.profile = await fetchProfile()
      return this.profile
    },
    async updateNickname(nickname: string) {
      await updateProfile({ nickname })
      if (this.profile) this.profile.nickname = nickname
    },
    clear() {
      this.profile = null
    },
  },
})
