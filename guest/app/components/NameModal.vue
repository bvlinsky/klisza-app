<template>
  <!-- Inline container, normal div — not a modal -->
  <div v-if="isOpen" class="mx-auto w-full max-w-md px-4">
    <div class="relative overflow-hidden bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl shadow-xl w-full">
      <!-- Retro Header -->
      <div class="bg-gradient-to-r from-amber-400 to-orange-500 px-6 py-6 text-center">
        <div class="inline-block p-3 bg-white/20 rounded-full backdrop-blur-sm mb-3">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-white drop-shadow-lg" style="font-family: 'Courier New', monospace">
          Jak masz na imię?
        </h2>
      </div>

      <!-- Content -->
      <div class="px-6 py-8">
        <UForm :state="formState" @submit="handleSubmit" class="space-y-5">
          <div class="space-y-2">
            <UFormGroup :error="validationErrors.name?.[0]">
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

            <p class="text-xs text-center text-gray-500">
              Twoje imię zostanie użyte do identyfikacji zdjęć
            </p>
          </div>

          <UButton
            type="submit"
            size="xl"
            class="w-full"
            color="primary"
            variant="solid"
            :loading="isLoading"
            :disabled="!formData.name.trim()"
          >
            <span class="text-lg font-bold">Zacznij robić zdjęcia! 📸</span>
          </UButton>
        </UForm>

        <div class="mt-6 bg-amber-100/50 rounded-xl p-4 border border-amber-200">
          <p class="text-xs text-center text-gray-600">
            🔒 Twoje imię będzie zapisane bezpiecznie
          </p>
        </div>
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
  isLoading?: boolean
  validationErrors?: Record<string, string[]>
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'submit', name: string): void
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
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
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

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
