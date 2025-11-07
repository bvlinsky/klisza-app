<template>
  <div class="fixed inset-0 overflow-hidden bg-black">
    <div class="mx-auto w-full max-w-sm h-full">
    <!-- Welcome Modal with name input - First time visit -->
    <WelcomeModal
      v-model="uiStore.showWelcomeModal"
      :event-name="eventStore.event?.name"
      :event-date="eventStore.event?.date"
      :is-loading="uiStore.isAuthenticating"
      :validation-errors="authValidationErrors"
      @submit="handleNameSubmit"
    />

    <!-- Retro Camera Interface - Main view after auth -->
    <RetroCameraView
      v-if="!uiStore.showWelcomeModal && eventStore.event"
      :quota-remaining="eventStore.quotaRemaining"
      :is-uploading="uiStore.isUploading"
      @capture="handleCapture"
    />
    </div>
  </div>
</template>

<script setup lang="ts">
import { useHead } from '#imports'
import { useEventStore } from '~/stores/event'
import { useUiStore } from '~/stores/ui'
import { api } from '~/composables/useApi'
import { useAppToast } from '~/composables/useToast'

// Composables
const route = useRoute()
const eventId = route.params.event_id as string
const { showError, showWarning } = useAppToast()

// Stores
const eventStore = useEventStore()
const uiStore = useUiStore()

// Reactive state
const authValidationErrors = ref<Record<string, string[]>>({})

// Methods

const handleNameSubmit = async (name: string) => {
  uiStore.setAuthenticating(true)
  authValidationErrors.value = {}

  try {
    const response = await api.authenticateGuest(eventId, name)

    if (response.error) {
      // Handle API errors
      const status = response.response.status
      const errorBody = response.error as { message?: string; errors?: Record<string, string[]> } | undefined
      if (status === 422 && errorBody?.errors) {
        // Validation errors
        authValidationErrors.value = errorBody.errors
      } else {
        // Other errors - show toast
        console.error('Authentication error:', { status, error: response.error })
        showError('Nie udało się zalogować. Spróbuj ponownie.')
      }
      return
    }

    if (response.data) {
      // Success
      eventStore.setAuthData(response.data)
      uiStore.hideWelcome()
    }
  } catch (error) {
    console.error('Network error during authentication:', error)
    showError('Problem z połączeniem internetowym. Sprawdź połączenie i spróbuj ponownie.')
  } finally {
    uiStore.setAuthenticating(false)
  }
}

const handleCapture = async (photoBlob: Blob, takenAt: string) => {
  uiStore.setUploading(true)

  try {
    console.log('Upload photo:', { eventId, fileSize: photoBlob.size, takenAt })

    const response = await api.uploadPhoto(eventId, photoBlob, takenAt)

    if (response.error) {
      // Handle upload errors
      const status = response.response.status
      if (status === 401) {
        // Token expired - clear auth and show name modal
        eventStore.clearAuth()
        uiStore.showNameInput()
        showWarning('Sesja wygasła. Zaloguj się ponownie.')
      } else if (status === 403) {
        // Upload window closed
        console.error('Upload window closed')
        showError('Okno na wysyłanie zdjęć zostało zamknięte przez organizatora.')
      } else if (status === 422) {
        // Validation error
        const errorBody = response.error as { message?: string; errors?: Record<string, string[]> } | undefined
        console.error('Upload validation error:', errorBody?.errors)
        showError('Zdjęcie nie spełnia wymagań. Spróbuj zrobić inne zdjęcie.')
      } else if (status === 429) {
        // Quota exceeded
        console.error('Upload quota exceeded')
        showError('Osiągnięto limit zdjęć na dzisiaj.')
      } else {
        console.error('Upload error:', { status, error: response.error })
        showError('Nie udało się wysłać zdjęcia. Spróbuj ponownie.')
      }
      return
    }

    if (response.data) {
      // Success - update quota
      const newQuota = parseInt(response.data.quota_remaining)
      eventStore.updateQuota(newQuota)
    }
  } catch (error) {
    console.error('Network error during upload:', error)
    showError('Problem z połączeniem internetowym. Sprawdź połączenie i spróbuj ponownie.')
  } finally {
    uiStore.setUploading(false)
  }
}

// Lifecycle
onMounted(async () => {
  try {
    const response = await api.getEvent(eventId)

    if (response.error) {
      // Handle API errors
      const status = response.response.status
      if (status === 404) {
        // Event not found
        console.error('Event not found:', eventId)
        showError('Wydarzenie nie zostało znalezione.')
        // TODO: Redirect to not found page
      } else {
        console.error('Error fetching event:', { status, error: response.error })
        showError('Nie udało się pobrać danych wydarzenia. Odśwież stronę.')
      }
      return
    }

    if (response.data) {
      eventStore.setEvent(response.data)

      // Check if user has a token for this event
      const storedToken = localStorage.getItem(`analog_snap_token_${eventId}`)
      console.log('Stored token for event', eventId, ':', storedToken)

      if (!storedToken) {
        console.log('No token found, showing welcome modal')
        uiStore.showWelcome()
      } else {
        console.log('Token found, loading auth data and skipping modal')
        // Load persisted quota for this event if available
        const storedQuota = localStorage.getItem(`analog_snap_quota_${eventId}`)
        const persistedQuota = storedQuota ? parseInt(storedQuota, 10) : 15

        eventStore.setAuthData({
          access_token: storedToken,
          quota_remaining: Number.isNaN(persistedQuota) ? 15 : persistedQuota
        })
      }
    }
  } catch (error) {
    console.error('Network error fetching event:', error)
    showError('Problem z połączeniem internetowym. Sprawdź połączenie i odśwież stronę.')
  }
})

// Head
useHead({
  title: eventStore.event?.name || 'klisza.app',
  meta: [
    { name: 'viewport', content: 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' },
    { name: 'theme-color', content: '#000000' },
  ],
})
</script>
