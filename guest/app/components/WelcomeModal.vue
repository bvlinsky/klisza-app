<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-hidden" @click.stop>
      <!-- Retro Header -->
      <div class="bg-gradient-to-r from-amber-400 to-orange-500 px-6 py-6 text-center">
        <div class="inline-block p-3 bg-white/20 rounded-full backdrop-blur-sm mb-3">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-white drop-shadow-lg" style="font-family: 'Courier New', monospace">
          {{ eventName }}
        </h1>
        <p class="text-white/90 text-sm drop-shadow" style="font-family: 'Courier New', monospace">
          {{ formatDate(eventDate) }}
        </p>
      </div>

      <!-- Content -->
      <div class="px-6 py-8">
        <!-- Instructions -->
        <div class="text-center space-y-4 mb-6">

          <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 border-2 border-amber-200">
            <p class="text-sm text-gray-700 leading-relaxed">
              Ta aplikacja działa jak aparat jednorazowy.<br>
              Zrób zdjęcie przyciskiem aparatu - wyśle się automatycznie!
            </p>
          </div>
        </div>

        <!-- Name Input -->
        <UForm :state="formState" @submit.prevent="handleSubmit" class="space-y-4">
          <div class="space-y-2">
            <UFormGroup
              label="Jak masz na imię?"
              :error="validationErrors.name?.[0]"
            >
              <UInput
                v-model="formData.name"
                placeholder="Wpisz swoje imię..."
                size="xl"
                autofocus
                :class="{ 'ring-red-500': validationErrors.name }"
                class="text-center text-lg font-semibold w-full"
                style="font-family: 'Courier New', monospace"
              />
              <template v-if="validationErrors.name?.[0]" #error>
                <div class="text-center mt-2">
                  <span
                    class="text-sm text-red-600 bg-red-50 px-3 py-1 rounded-full"
                    role="alert"
                    aria-live="polite"
                  >
                    {{ validationErrors.name[0] }}
                  </span>
                </div>
              </template>
            </UFormGroup>
          </div>

          <button
            type="submit"
            class="w-full px-6 py-4 bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white font-bold text-lg rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
            :disabled="!formData.name.trim() || isLoading"
          >
            <span v-if="isLoading" class="flex items-center justify-center gap-2">
              <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              Ładowanie...
            </span>
            <span v-else class="text-lg font-bold">Rozpocznij robienie zdjęć! 📸</span>
          </button>
        </UForm>
      </div>

      <!-- Retro decoration -->
      <div class="absolute top-0 right-0 w-24 h-24 bg-amber-400/10 rounded-full -translate-y-12 translate-x-12"></div>
      <div class="absolute bottom-0 left-0 w-20 h-20 bg-orange-400/10 rounded-full translate-y-10 -translate-x-10"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'

interface Props {
  modelValue: boolean
  eventName?: string
  eventDate?: string
  isLoading?: boolean
  validationErrors?: Record<string, string[]>
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'submit', name: string): void
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  eventName: '',
  eventDate: '',
  isLoading: false,
  validationErrors: () => ({}),
})

const emit = defineEmits<Emits>()

// Reactive state
const formData = ref({
  name: '',
})

const formState = ref({
  name: null,
})

// Computed
const isOpen = computed({
  get: () => {
    console.log('WelcomeModal isOpen getter:', props.modelValue)
    return props.modelValue
  },
  set: (value) => {
    console.log('WelcomeModal isOpen setter:', value)
    emit('update:modelValue', value)
  }
})

const formatDate = (dateString?: string) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('pl-PL', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Methods
const handleSubmit = () => {
  if (!formData.value.name.trim()) return

  emit('submit', formData.value.name.trim())
}

// Watchers
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue) {
      // Clear form when modal opens
      formData.value.name = ''
      formState.value.name = null
    }
  }
)
</script>
