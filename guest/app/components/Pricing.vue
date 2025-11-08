<script setup lang="ts">
const runtimeConfig = useRuntimeConfig();

const tiers = ref([
  {
    id: 'basic',
    title: 'Basic',
    price: '99 zł',
    description: 'Wszystko co potrzebne na małą imprezę.',
    billingCycle: '/wydarzenie',
    button: {
      label: 'Zamów teraz',
      to: runtimeConfig.public.stripeOption1Url,
    }
  },
  {
    id: 'standard',
    title: 'Standard',
    price: '159 zł',
    description: 'Dodatkowe usprawnienia i komfort użycia.',
    billingCycle: '/wydarzenie',
    badge: 'Najczęściej wybierany',
    highlight: true,
    button: {
      label: 'Zamów teraz',
      to: runtimeConfig.public.stripeOption2Url,
    }
  },
  {
    id: 'premium',
    title: 'Premium',
    price: '199 zł',
    description: 'Najpełniejsze doświadczenie w retro stylu.',
    billingCycle: '/wydarzenie',
    button: {
      label: 'Zamów teraz',
      to: runtimeConfig.public.stripeOption3Url,
    }
  }
])

const sections = ref([
  {
    title: 'Dla gości',
    features: [
      {
        title: 'Maksymalna liczba gości',
        tiers: { basic: 30, standard: 50, premium: 'Bez limitu' }
      },
      {
        title: 'Limit zdjęć na osobę',
        tiers: { basic: 15, standard: 15, premium: 20 }
      },
      {
        title: 'Aktywność galerii po publikacji (dni)',
        tiers: { basic: 30, standard: 30, premium: 90 }
      },
    ]
  },
  {
    title: 'Dla organizatorów',
    features: [
      {
        title: 'Unikalny link / kod qr dla gości',
        tiers: { basic: true, standard: true, premium: true }
      },
      {
        title: 'Moderacja zdjęć (podgląd, usuwanie)',
        tiers: { basic: true, standard: true, premium: true }
      },
      {
        title: 'Publikacja galerii',
        tiers: { basic: true, standard: true, premium: true }
      },
    ]
  }
])
</script>

<template>
  <UContainer id="cennik" class="px-6 py-12">
    <h2 class="text-3xl font-bold mb-8">Cennik</h2>
    <UPricingTable :tiers="tiers" :sections="sections" />
  </UContainer>
</template>
