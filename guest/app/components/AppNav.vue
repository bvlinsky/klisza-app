<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const items = computed<NavigationMenuItem[]>(() => [
  {
    label: 'Jak to działa?',
    to: '/#jak-to-dziala',
    active: false
  },
  {
    label: 'Cennik',
    to: '/#cennik',
    active: false
  },
  {
    label: 'FAQ',
    to: '/#faq',
    active: false
  },
])
</script>

<template>
  <UHeader>
    <template #title>
      <img src="/images/logo.png" alt="klisza.app" class="h-8" />
    </template>

    <template #right>
      <UNavigationMenu :items="items" />
    </template>
  </UHeader>
</template>
