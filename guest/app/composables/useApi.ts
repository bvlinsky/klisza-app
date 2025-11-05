import createClient from 'openapi-fetch'
import type { paths } from './../schema'

// Create API client
const client = createClient<paths>({
  baseUrl: `${useRuntimeConfig().public.apiBaseUrl}/api`,
})

// Enhanced client with auth headers
export const useApi = () => {
  const { $pinia } = useNuxtApp()
  const eventStore = useEventStore()

  // Create authenticated client
  const authenticatedClient = createClient<paths>({
    baseUrl: `${useRuntimeConfig().public.apiBaseUrl}/api`,
    headers: {
      get Authorization() {
        return eventStore.accessToken ? `Bearer ${eventStore.accessToken}` : undefined
      },
    },
  })

  return {
    client,
    authenticatedClient,
  }
}

// API methods
export const api = {
  // Get event details
  getEvent: async (eventId: string) => {
    const { authenticatedClient } = useApi()
    return authenticatedClient.GET('/events/{event}', {
      params: {
        path: { event: eventId }
      }
    })
  },

  // Authenticate guest
  authenticateGuest: async (eventId: string, name: string) => {
    const { client } = useApi()
    return client.POST('/events/{event}/auth', {
      params: {
        path: { event: eventId }
      },
      body: {
        name
      }
    })
  },

  // Upload photo
  uploadPhoto: async (eventId: string, file: Blob, takenAt: string) => {
    const { authenticatedClient } = useApi()

    // Create FormData for multipart upload
    const formData = new FormData()
    formData.append('file', file, 'photo.jpg')
    formData.append('taken_at', takenAt)

    return authenticatedClient.POST('/events/{event}/photos', {
      params: {
        path: { event: eventId }
      },
      body: formData as any
    })
  },
}

export default api
