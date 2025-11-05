import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUiStore = defineStore('ui', () => {
  // State
  const showWelcomeModal = ref(false)
  const showNameModal = ref(false)
  const isAuthenticating = ref(false)
  const isUploading = ref(false)
  const isLoading = ref(false)

  // Actions
  const showWelcome = () => {
    showWelcomeModal.value = true
    showNameModal.value = false
  }

  const hideWelcome = () => {
    showWelcomeModal.value = false
  }

  const showNameInput = () => {
    showNameModal.value = true
  }

  const hideNameInput = () => {
    showNameModal.value = false
  }

  const setAuthenticating = (value: boolean) => {
    isAuthenticating.value = value
  }

  const setUploading = (value: boolean) => {
    isUploading.value = value
  }

  const setLoading = (value: boolean) => {
    isLoading.value = value
  }

  const reset = () => {
    showWelcomeModal.value = true
    showNameModal.value = false
    isAuthenticating.value = false
    isUploading.value = false
    isLoading.value = false
  }

  return {
    // State
    showWelcomeModal,
    showNameModal,
    isAuthenticating,
    isUploading,
    isLoading,

    // Actions
    showWelcome,
    hideWelcome,
    showNameInput,
    hideNameInput,
    setAuthenticating,
    setUploading,
    setLoading,
    reset,
  }
})
