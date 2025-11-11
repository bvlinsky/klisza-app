<template>
  <div class="min-h-screen">
    <!-- Nav -->
    <AppNav />

    <!-- Hero -->
    <UPageHero
      title="Aparat jednorazowy w telefonie Twoich gości"
      description="Zeskanuj kod QR, aby robić zdjęcia."
      orientation="horizontal"
      :links="links"
    >
      <img
        src="/images/hero.png"
      />
    </UPageHero>

    <HowItWorks />

    <footer class="px-6 py-10 border-t border-white/10 text-center text-white/60 text-sm">
      <p>© {{ new Date().getFullYear() }} klisza.app - wspólne wspomnienia w retro stylu.</p>
    </footer>
  </div>

</template>

<script setup lang="ts">
const links = ref([
  {
    label: 'Jak to działa?',
    to: '#jak-to-dziala',
    color: 'neutral' as const,
    variant: 'subtle' as const,
  }
])
</script>
