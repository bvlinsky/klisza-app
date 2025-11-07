<template>
  <div class="bg-black flex flex-col w-full h-full">

    <!-- Camera Preview Area -->
    <div class="flex-1 relative bg-black overflow-hidden flex items-center justify-center">
      <!-- Video Stream Container with 3:4 aspect ratio (phone portrait) -->
      <div class="relative w-full max-w-full aspect-[3/4] bg-black">
        <video
          ref="videoRef"
          class="absolute inset-0 w-full h-full object-cover"
          :class="{ 'opacity-0': !isCameraReady }"
          autoplay
          playsinline
          muted
          aria-label="Podgląd kamery"
        ></video>

        <!-- Loading State -->
        <div
          v-if="!isCameraReady"
          class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-amber-900/50 to-orange-900/50"
        >
          <div class="text-center space-y-4">
            <div class="w-20 h-20 mx-auto rounded-full bg-amber-400/20 flex items-center justify-center">
              <svg class="w-10 h-10 text-amber-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <p 
              class="text-amber-400 text-sm"
              style="font-family: 'Courier New', monospace"
              role="status"
              aria-live="polite"
            >
              {{ cameraStatus }}
            </p>
          </div>
        </div>

        <!-- Retro Viewfinder Overlay -->
        <div
            v-if="isCameraReady"
            class="absolute inset-0 pointer-events-none"
        >
          <!-- Corner brackets (like old cameras) -->
          <div class="absolute top-8 left-8 w-12 h-12 border-l-4 border-t-4 border-amber-400/60"></div>
          <div class="absolute top-8 right-8 w-12 h-12 border-r-4 border-t-4 border-amber-400/60"></div>
          <div class="absolute bottom-8 left-8 w-12 h-12 border-l-4 border-b-4 border-amber-400/60"></div>
          <div class="absolute bottom-8 right-8 w-12 h-12 border-r-4 border-b-4 border-amber-400/60"></div>
          
          <!-- Center crosshair -->
          <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
            <div class="w-1 h-8 bg-amber-400/40 mx-auto"></div>
            <div class="w-8 h-1 bg-amber-400/40 -mt-4.5"></div>
          </div>

          <!-- Film grain effect -->
          <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMDAiIGhlaWdodD0iMzAwIj48ZmlsdGVyIGlkPSJhIj48ZmVUdXJidWxlbmNlIGJhc2VGcmVxdWVuY3k9Ii45IiBudW1PY3RhdmVzPSI1Ii8+PGZlQ29sb3JNYXRyaXggdHlwZT0ic2F0dXJhdGUiIHZhbHVlcz0iMCIvPjwvZmlsdGVyPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbHRlcj0idXJsKCNhKSIgb3BhY2l0eT0iLjA1Ii8+PC9zdmc+')] opacity-30 mix-blend-overlay"></div>
        </div>

        <!-- Capturing Overlay -->
        <div
          v-if="isCapturing"
          class="absolute inset-0 bg-white/80 flex items-center justify-center z-10"
        >
          <div class="text-center space-y-3">
            <div class="w-16 h-16 mx-auto rounded-full border-4 border-amber-400 border-t-transparent animate-spin"></div>
            <p class="text-amber-600 font-bold text-lg" style="font-family: 'Courier New', monospace">
              Przetwarzanie...
            </p>
          </div>
        </div>

        <!-- Flash Effect -->
        <Transition
          enter-active-class="transition-opacity duration-100"
          leave-active-class="transition-opacity duration-300"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div v-if="showFlash" class="absolute inset-0 bg-white z-20"></div>
        </Transition>
      </div>
    </div>

    <!-- Bottom Controls - like phone camera app -->
    <div class="bg-gradient-to-t from-black via-black/95 to-black/90 px-6 py-8">
      <div class="max-w-md mx-auto">
        <!-- Shutter Button -->
        <div class="flex items-center justify-center gap-8">
          <!-- Counter Left -->
          <div class="w-16 text-center">
            <p class="text-xs text-gray-500">pozostało</p>
            <p class="text-2xl font-bold text-amber-400" style="font-family: 'Courier New', monospace">
              {{ quotaRemaining }}
            </p>
          </div>

          <!-- Main Shutter Button -->
          <button
            @click="handleCapture"
            class="relative w-20 h-20 rounded-full transition-all duration-150 active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed"
            :class="{
              'shadow-[0_0_30px_rgba(251,191,36,0.5)]': isCameraReady && !isCapturing && hasQuota,
              'animate-pulse': isCapturing
            }"
            :disabled="!isCameraReady || isCapturing || !hasQuota"
            :aria-label="isCapturing ? 'Przetwarzanie zdjęcia...' : hasQuota ? 'Zrób zdjęcie' : 'Osiągnięto limit zdjęć'"
            :aria-describedby="hasQuota ? undefined : 'quota-limit-message'"
          >
            <!-- Outer ring -->
            <div class="absolute inset-0 rounded-full border-4 border-amber-400"></div>
            <!-- Inner button -->
            <div 
              class="absolute inset-2 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 transition-all"
              :class="{ 'scale-0': isCapturing }"
            ></div>
            <!-- Click effect -->
            <div 
              v-if="!isCapturing"
              class="absolute inset-3 rounded-full bg-white/30"
            ></div>
          </button>

          <!-- Status Right -->
          <div class="w-16 text-center">
            <div 
              v-if="!hasQuota"
              id="quota-limit-message"
              class="text-xs text-red-400"
              role="status"
              aria-live="polite"
            >
              Limit!
            </div>
            <div 
              v-else-if="!isCameraReady"
              class="text-xs text-amber-400 animate-pulse"
              role="status"
              aria-live="polite"
            >
              Czekaj...
            </div>
            <div v-else class="w-3 h-3 bg-green-400 rounded-full mx-auto"></div>
          </div>
        </div>

        <!-- Hint Text -->
        <p 
          class="text-center text-xs text-gray-500 mt-4"
          style="font-family: 'Courier New', monospace"
        >
          {{ hintText }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  quotaRemaining: number
  isUploading?: boolean
}

interface Emits {
  (e: 'capture', blob: Blob, takenAt: string): void
}

const props = withDefaults(defineProps<Props>(), {
  quotaRemaining: 15,
  isUploading: false,
})

const emit = defineEmits<Emits>()

const { showError } = useAppToast()

// Template refs
const videoRef = ref<HTMLVideoElement>()

// Reactive state
const isCameraReady = ref(false)
const isCapturing = ref(false)
const showFlash = ref(false)
const cameraStatus = ref('Przygotowywanie kamery...')

// Computed
const hasQuota = computed(() => props.quotaRemaining > 0)

const hintText = computed(() => {
  if (!hasQuota.value) return 'Osiągnięto limit zdjęć'
  if (!isCameraReady.value) return 'Ładowanie kamery...'
  if (isCapturing.value) return 'Przetwarzanie zdjęcia...'
  return `Dotknij przycisku aby zrobić zdjęcie`
})

// Methods
const handleCapture = async () => {
  if (!isCameraReady.value || isCapturing.value || !hasQuota.value) return

  isCapturing.value = true
  showFlash.value = true

  // Flash effect
  setTimeout(() => {
    showFlash.value = false
  }, 100)

  try {
    const photoBlob = await capturePhoto()
    const now = new Date()
    const compressedBlob = await compressPhoto(photoBlob, now)

    // Emit capture event with string timestamp for API (YYYY-MM-DD HH:mm:ss)
    const takenAtStr = now.toISOString().slice(0, 19).replace('T', ' ')
    emit('capture', compressedBlob, takenAtStr)
  } catch (error) {
    console.error('Photo capture error:', error)
    showError('Nie udało się zrobić zdjęcia. Spróbuj ponownie.')
  } finally {
    isCapturing.value = false
  }
}

const capturePhoto = (): Promise<Blob> => {
  return new Promise((resolve, reject) => {
    if (!videoRef.value) {
      reject(new Error('Video element not available'))
      return
    }

    const srcW = videoRef.value.videoWidth
    const srcH = videoRef.value.videoHeight
    const targetRatio = 3 / 4
    const srcRatio = srcW / srcH

    let sx = 0
    let sy = 0
    let sWidth = srcW
    let sHeight = srcH

    if (srcRatio > targetRatio) {
      sWidth = Math.round(srcH * targetRatio)
      sx = Math.floor((srcW - sWidth) / 2)
    } else if (srcRatio < targetRatio) {
      sHeight = Math.round(srcW / targetRatio)
      sy = Math.floor((srcH - sHeight) / 2)
    }

    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')

    if (!ctx) {
      reject(new Error('Canvas context not available'))
      return
    }

    canvas.width = sWidth
    canvas.height = sHeight

    ctx.drawImage(
      videoRef.value,
      sx,
      sy,
      sWidth,
      sHeight,
      0,
      0,
      sWidth,
      sHeight
    )

    canvas.toBlob(
      (blob) => {
        if (blob) {
          resolve(blob)
        } else {
          reject(new Error('Failed to create photo blob'))
        }
      },
      'image/jpeg',
      0.95
    )
  })
}

const compressPhoto = async (photoBlob: Blob, takenAt: Date): Promise<Blob> => {
  return new Promise((resolve) => {
    const img = new Image()
    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')

    img.onload = async () => {
      if (!ctx) {
        resolve(photoBlob) // Return original if compression fails
        return
      }

      // Calculate new dimensions (max 1920px width, maintain aspect ratio)
      const maxWidth = 1920
      const maxHeight = 2560
      let { width, height } = img

      if (width > maxWidth) {
        height = (height * maxWidth) / width
        width = maxWidth
      }

      if (height > maxHeight) {
        width = (width * maxHeight) / height
        height = maxHeight
      }

      canvas.width = width
      canvas.height = height

      // Draw and compress
      ctx.drawImage(img, 0, 0, width, height)

      // Format stamp as: D M YY HH:mm in Europe/Warsaw time (compact)
      const fmt = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Europe/Warsaw',
        year: '2-digit',
        month: 'numeric',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
      })
      const formatted = fmt.format(takenAt)
      const nums = formatted.match(/\d+/g) ?? []
      const dateStr = nums.length >= 5
        ? `${+nums[0]} ${+nums[1]} ${nums[2]}  ${nums[3]}:${nums[4]}`
        : formatted.replace(',', '')

      const fontSize = Math.round(width * 0.035)
      const margin = Math.round(width * 0.05)

      ctx.save()
      // Preload SevenSegment font before drawing, if supported
      try {
        if (document && (document as any).fonts?.load) {
          await (document as any).fonts.load(`normal ${fontSize}px "SevenSegment"`)
        }
        if (document && (document as any).fonts?.ready) {
          await (document as any).fonts.ready
        }
      } catch (_) {
        // Non-fatal: fall back to monospace if font fails to load
      }

      ctx.font = `normal ${fontSize}px "SevenSegment", monospace`
      ctx.textAlign = 'right'
      ctx.textBaseline = 'bottom'

      // Analog luminous orange date: layered glow passes to match vintage imprint
      const x = width - margin
      const y = height - margin
      ctx.globalCompositeOperation = 'lighter'
      ctx.lineJoin = 'round'

      // Far glow (soft, wide) — slightly more orange
      ctx.shadowColor = 'rgba(255, 112, 0, 0.45)'
      ctx.shadowBlur = Math.round(fontSize * 0.45)
      ctx.fillStyle = 'rgba(255, 112, 0, 0.12)'
      ctx.fillText(dateStr, x, y)

      // Mid glow (stronger, tighter) — slightly more orange
      ctx.shadowColor = 'rgba(255, 150, 0, 0.45)'
      ctx.shadowBlur = Math.round(fontSize * 0.22)
      ctx.fillStyle = 'rgba(255, 150, 0, 0.24)'
      ctx.fillText(dateStr, x, y)

      // Edge highlight ring — slightly more orange
      ctx.shadowColor = 'rgba(255, 160, 0, 0.35)'
      ctx.shadowBlur = Math.round(fontSize * 0.10)
      ctx.strokeStyle = 'rgba(255, 160, 0, 0.75)'
      ctx.lineWidth = Math.max(2, Math.round(fontSize * 0.08))
      ctx.strokeText(dateStr, x, y)

      // Core hot amber — slightly more orange
      ctx.shadowColor = 'rgba(255, 190, 100, 0.45)'
      ctx.shadowBlur = Math.round(fontSize * 0.06)
      ctx.fillStyle = 'rgba(255, 190, 100, 0.88)'
      ctx.fillText(dateStr, x, y)

      // Reset blend
      ctx.globalCompositeOperation = 'source-over'
      ctx.restore()

      canvas.toBlob(
        (compressedBlob) => {
          if (compressedBlob && compressedBlob.size < photoBlob.size) {
            resolve(compressedBlob)
          } else {
            resolve(photoBlob) // Return original if compression didn't help
          }
        },
        'image/jpeg',
        0.85
      )
    }

    img.src = URL.createObjectURL(photoBlob)
  })
}

// Camera initialization
const initCamera = async () => {
  try {
    cameraStatus.value = 'Uzyskiwanie dostępu do kamery...'

    const stream = await navigator.mediaDevices.getUserMedia({
      video: {
        facingMode: 'environment', // Prefer back camera
        aspectRatio: { ideal: 3 / 4 }, // 3:4 for phone portrait orientation
        width: { ideal: 1080 },
        height: { ideal: 1440 },
      },
    })

    if (videoRef.value) {
      videoRef.value.srcObject = stream
      await videoRef.value.play()
      isCameraReady.value = true
      cameraStatus.value = ''
    }
  } catch (error) {
    console.error('Camera access error:', error)
    cameraStatus.value = 'Brak dostępu do kamery'
    isCameraReady.value = false
    showError('Nie można uzyskać dostępu do kamery. Sprawdź uprawnienia.')
  }
}

// Lifecycle
onMounted(() => {
  initCamera()
})

onUnmounted(() => {
  // Clean up camera stream
  if (videoRef.value?.srcObject) {
    const stream = videoRef.value.srcObject as MediaStream
    stream.getTracks().forEach((track) => track.stop())
  }
})
</script>

