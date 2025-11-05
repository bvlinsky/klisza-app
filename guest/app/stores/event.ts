import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export interface Event {
  id: string
  name: string
  date: string
  gallery_published: boolean
}

export interface AuthResponse {
  access_token: string
  quota_remaining: number
}

export interface PhotoUploadResponse {
  quota_remaining: string
}

export const useEventStore = defineStore('event', () => {
  // State
  const event = ref<Event | null>(null)
  const accessToken = ref<string | null>(null)
  const quotaRemaining = ref<number>(15)
  const isAuthenticated = ref(false)

  // Getters
  const isAuthRequired = computed(() => {
    return !accessToken.value
  })

  const hasQuota = computed(() => {
    return quotaRemaining.value > 0
  })

  // Actions
  const setEvent = (eventData: Event) => {
    event.value = eventData
  }

  const setAuthData = (authData: AuthResponse) => {
    accessToken.value = authData.access_token
    quotaRemaining.value = authData.quota_remaining
    isAuthenticated.value = true

    // Store token in localStorage with event-specific key
    if (event.value) {
      localStorage.setItem(`analog_snap_token_${event.value.id}`, authData.access_token)
    }
  }

  const updateQuota = (remaining: number) => {
    quotaRemaining.value = remaining
  }

  const loadStoredToken = (eventId: string) => {
    const storedToken = localStorage.getItem(`analog_snap_token_${eventId}`)
    if (storedToken) {
      accessToken.value = storedToken
      isAuthenticated.value = true
    }
  }

  const clearAuth = () => {
    accessToken.value = null
    isAuthenticated.value = false
    quotaRemaining.value = 15

    // Clear from localStorage
    if (event.value) {
      localStorage.removeItem(`analog_snap_token_${event.value.id}`)
    }
  }

  const reset = () => {
    event.value = null
    accessToken.value = null
    quotaRemaining.value = 15
    isAuthenticated.value = false
  }

  return {
    // State
    event,
    accessToken,
    quotaRemaining,
    isAuthenticated,

    // Getters
    isAuthRequired,
    hasQuota,

    // Actions
    setEvent,
    setAuthData,
    updateQuota,
    loadStoredToken,
    clearAuth,
    reset,
  }
})
