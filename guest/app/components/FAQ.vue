<template>
  <section id="faq" class="py-16 px-6">
    <div class="max-w-4xl mx-auto">
      <h2 class="text-3xl font-bold text-center mb-12">Najczęściej zadawane pytania</h2>
      
      <UAccordion
        :items="faqItems"
        :ui="{
          item: {
            base: 'border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden mb-4'
          },
          header: 'p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors',
          body: 'p-4 text-gray-600 dark:text-gray-300'
        }"
      >
        <template #default="{ item, open }">
          <div class="flex items-center justify-between">
            <span class="font-medium">{{ item.label }}</span>
            <UIcon
              name="i-heroicons-chevron-right"
              class="w-5 h-5 transition-transform"
              :class="[open && 'rotate-90']"
            />
          </div>
        </template>
        
        <template #body="{ item }">
          <p>{{ item.content }}</p>
        </template>
      </UAccordion>
    </div>
  </section>
</template>

<script setup lang="ts">
const faqItems = [
  {
    label: 'Czy goście muszą instalować aplikację?',
    content: 'Nie! Aplikacja działa w przeglądarce mobilnej, bez żadnej instalacji. Goście otrzymują link i od razu mogą przesyłać zdjęcia w retro stylu.'
  },
  {
    label: 'Ile zdjęć mogę przesłać jako gość?',
    content: 'W planach Basic i Standard każdy gość może przesłać maksymalnie 15 zdjęć. W planie Premium limit wzrasta do 20 zdjęć na osobę.'
  },
  {
    label: 'Jaka jest maksymalna liczba gości?',
    content: 'Plan Basic obsługuje do 30 gości, Standard do 50 gości, a Premium oferuje nieograniczoną liczbę uczestników.'
  },
  {
    label: 'Jak długo dostępna jest galeria po publikacji?',
    content: 'W planach Basic i Standard galeria pozostaje aktywna przez 30 dni od publikacji. Plan Premium wydłuża ten czas do 90 dni.'
  },
  {
    label: 'Czy jako organizator mogę usuwać niechciane zdjęcia?',
    content: 'Tak! We wszystkich planach masz pełną kontrolę nad zawartością galerii. Przed publikacją możesz przeglądać wszystkie zdjęcia i usuwać te, które nie pasują do Twojej wizji.'
  },
  {
    label: 'Kiedy goście zobaczą gotową galerię?',
    content: 'Galeria będzie dostępna dla gości dopiero po jej opublikowaniu przez organizatora. To Ty decydujesz, kiedy udostępnić wspomnienia wszystkim uczestnikom.'
  },
  {
    label: 'Czy otrzymam kod QR dla gości?',
    content: 'Tak, we wszystkich planach otrzymasz unikalny link oraz kod QR, które możesz łatwo udostępnić uczestnikom imprezy.'
  },
  {
    label: 'Jaki jest koszt korzystania z aplikacji?',
    content: 'Oferujemy trzy plany cenowe: Basic za 99 zł, Standard za 159 zł i Premium za 199 zł za wydarzenie. Każdy plan zawiera wszystkie niezbędne funkcje do zebrania wspomnień z imprezy.'
  }
]
</script>