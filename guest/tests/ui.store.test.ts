import { describe, it, expect, beforeEach } from 'bun:test'
import { setActivePinia, createPinia } from 'pinia'
import { useUiStore } from '../app/stores/ui'

beforeEach(() => {
  setActivePinia(createPinia())
})

describe('useUiStore', () => {
  it('initial state is false for all flags', () => {
    const ui = useUiStore()
    expect(ui.showWelcomeModal).toBe(false)
    expect(ui.showNameModal).toBe(false)
    expect(ui.isAuthenticating).toBe(false)
    expect(ui.isUploading).toBe(false)
    expect(ui.isLoading).toBe(false)
  })

  it('showWelcome opens welcome and closes name modal', () => {
    const ui = useUiStore()
    ui.showWelcome()
    expect(ui.showWelcomeModal).toBe(true)
    expect(ui.showNameModal).toBe(false)
  })

  it('hideWelcome closes welcome modal', () => {
    const ui = useUiStore()
    ui.showWelcome()
    ui.hideWelcome()
    expect(ui.showWelcomeModal).toBe(false)
  })

  it('name input show/hide toggles correctly', () => {
    const ui = useUiStore()
    ui.showNameInput()
    expect(ui.showNameModal).toBe(true)
    ui.hideNameInput()
    expect(ui.showNameModal).toBe(false)
  })

  it('set flags and reset restores to defaults', () => {
    const ui = useUiStore()
    ui.setAuthenticating(true)
    ui.setUploading(true)
    ui.setLoading(true)
    expect(ui.isAuthenticating).toBe(true)
    expect(ui.isUploading).toBe(true)
    expect(ui.isLoading).toBe(true)

    ui.reset()
    expect(ui.showWelcomeModal).toBe(true)
    expect(ui.showNameModal).toBe(false)
    expect(ui.isAuthenticating).toBe(false)
    expect(ui.isUploading).toBe(false)
    expect(ui.isLoading).toBe(false)
  })
})