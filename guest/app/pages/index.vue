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
