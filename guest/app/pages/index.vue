<template>
  <div class="min-h-screen">
    <!-- Nav -->
    <AppNav />

    <!-- Hero -->
    <UPageHero
      title="Aparat jednorazowy w telefonie Twoich gości"
      orientation="horizontal"
      :links="links"
    >
      <img
        src="/images/hero.png"
      />
    </UPageHero>

    <!-- Jak to działa -->
    <HowItWorks />

    <Pricing />

    <!-- FAQ -->
    <FAQ />

    <!-- Footer -->
    <footer class="px-6 py-10 border-t border-white/10 text-center text-white/60 text-sm">
      <p>© {{ new Date().getFullYear() }} klisza.app - wspólne wspomnienia w retro stylu.</p>
    </footer>
  </div>

</template>

<script setup lang="ts">
useHead({
  title: 'klisza.app — Zbieraj zdjęcia z wesela',
  meta: [
    { name: 'description', content: 'Aplikacja do zdjęć weselnych dla gości - prosto i bez instalacji, w retro stylu.' },
    { name: 'viewport', content: 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' },
    { name: 'theme-color', content: '#000000' },
  ],
})

const links = ref([
  {
    label: 'Zamów teraz',
    to: '#cennik',
  },
  {
    label: 'Jak to działa?',
    to: '#jak-to-dziala',
    color: 'neutral' as const,
    variant: 'subtle' as const,
  }
])
</script>
