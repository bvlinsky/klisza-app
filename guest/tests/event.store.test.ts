import { describe, it, expect, beforeEach } from 'bun:test'
import { setActivePinia, createPinia } from 'pinia'
import { useEventStore } from '../app/stores/event'

let storage: Map<string, string>

beforeEach(() => {
  setActivePinia(createPinia())
  storage = new Map()
  // Minimal localStorage mock for tests
  // Bun may provide Web Storage, but we ensure a deterministic mock
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  ;(globalThis as any).localStorage = {
    getItem: (key: string) => (storage.has(key) ? storage.get(key)! : null),
    setItem: (key: string, value: string) => {
      storage.set(key, String(value))
    },
    removeItem: (key: string) => {
      storage.delete(key)
    },
    clear: () => {
      storage.clear()
    },
    key: (index: number) => Array.from(storage.keys())[index] ?? null,
    get length() {
      return storage.size
    },
  }
})

describe('useEventStore', () => {
  it('initial state and getters', () => {
    const s = useEventStore()
    expect(s.event).toBeNull()
    expect(s.accessToken).toBeNull()
    expect(s.quotaRemaining).toBe(15)
    expect(s.isAuthenticated).toBe(false)
    expect(s.isAuthRequired).toBe(true)
    expect(s.hasQuota).toBe(true)
  })

  it('setEvent and setAuthData persist to storage', () => {
    const s = useEventStore()
    const ev = { id: 'evt-1', name: 'Party', date: '2025-08-23', gallery_published: true }
    s.setEvent(ev)

    s.setAuthData({ access_token: 'token-abc', quota_remaining: 7 })
    expect(s.accessToken).toBe('token-abc')
    expect(s.quotaRemaining).toBe(7)
    expect(s.isAuthenticated).toBe(true)

    expect(storage.get(`analog_snap_token_${ev.id}`)).toBe('token-abc')
    expect(storage.get(`analog_snap_quota_${ev.id}`)).toBe('7')
  })

  it('updateQuota updates state and storage', () => {
    const s = useEventStore()
    const ev = { id: 'evt-2', name: 'Wedding', date: '2025-09-10', gallery_published: false }
    s.setEvent(ev)
    s.updateQuota(12)

    expect(s.quotaRemaining).toBe(12)
    expect(storage.get(`analog_snap_quota_${ev.id}`)).toBe('12')
  })

  it('loadStoredToken restores token and quota', () => {
    const s = useEventStore()
    const evId = 'evt-3'
    storage.set(`analog_snap_token_${evId}`, 'restored-token')
    storage.set(`analog_snap_quota_${evId}`, '10')

    s.loadStoredToken(evId)
    expect(s.accessToken).toBe('restored-token')
    expect(s.isAuthenticated).toBe(true)
    expect(s.quotaRemaining).toBe(10)
  })

  it('clearAuth resets values and removes storage items', () => {
    const s = useEventStore()
    const ev = { id: 'evt-4', name: 'Conference', date: '2025-12-01', gallery_published: false }
    s.setEvent(ev)
    s.setAuthData({ access_token: 'tok', quota_remaining: 5 })

    s.clearAuth()
    expect(s.accessToken).toBeNull()
    expect(s.isAuthenticated).toBe(false)
    expect(s.quotaRemaining).toBe(15)
    expect(storage.get(`analog_snap_token_${ev.id}`)).toBeUndefined()
    expect(storage.get(`analog_snap_quota_${ev.id}`)).toBeUndefined()
  })

  it('reset clears all state', () => {
    const s = useEventStore()
    s.setEvent({ id: 'evt-5', name: 'Festival', date: '2025-07-01', gallery_published: true })
    s.setAuthData({ access_token: 'tok2', quota_remaining: 3 })

    s.reset()
    expect(s.event).toBeNull()
    expect(s.accessToken).toBeNull()
    expect(s.quotaRemaining).toBe(15)
    expect(s.isAuthenticated).toBe(false)
  })
})