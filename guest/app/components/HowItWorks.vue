<template>
  <section id="jak-to-dziala" class="bg-white/5 border-t border-white/10">
    <UContainer class="px-6 py-12">
      <h2 class="text-3xl font-bold mb-8">Jak to działa?</h2>

      <div class="relative flex flex-col gap-8">
        <!-- Linia centralna osi czasu -->
        <div class="pointer-events-none absolute left-1/2 top-0 bottom-0 -translate-x-1/2 w-px bg-white/10"></div>

        <!-- Kroki -->
        <div
          v-for="(step, i) in steps"
          :key="i"
          :class="['relative z-10 pb-12 lg:w-1/2', i % 2 === 0 ? 'lg:mr-auto lg:pr-8' : 'lg:ml-auto lg:pl-8']"
        >
          <UCard>
            <div :class="['flex items-center gap-5', i % 2 === 0 ? 'lg:flex-row' : 'lg:flex-row-reverse']">
              <img :src="step.icon" :alt="'Krok ' + (i+1)" class="w-28 h-28 shrink-0 object-contain" />
              <div class="space-y-2">
                <p class="text-amber-400 font-medium tracking-wide text-sm">Krok {{ i + 1 }}</p>
                <h3 class="text-lg font-semibold">{{ step.title }}</h3>
                <p class="text-white/70 text-sm leading-relaxed">{{ step.desc }}</p>
              </div>
            </div>
          </UCard>
        </div>
      </div>
    </UContainer>
  </section>
</template>

<script setup lang="ts">
const steps = [
  {
    title: 'Zakładasz wydarzenie',
    desc: 'Podajesz nazwę i datę imprezy.',
    icon: '/images/step1.svg'
  },
  {
    title: 'Wysyłasz link do galerii',
    desc: 'Otrzymujesz prosty link, który możesz udostępnić gościom.',
    icon: '/images/step2.svg'
  },
  {
    title: 'Goście robią zdjęcia',
    desc: 'Wpisują swoje imię i korzystają z aparatu.',
    icon: '/images/step3.svg'
  },
  {
    title: 'Powstaje wspólna galeria',
    desc: 'Po imprezie przeglądasz zebrane zdjęcia w jednym miejscu.',
    icon: '/images/step4.svg'
  }
]
</script>