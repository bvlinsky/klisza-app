<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const panelUrl = useRuntimeConfig().public.apiBaseUrl

const items = computed<NavigationMenuItem[]>(() => [
  {
    label: 'Jak to działa?',
    to: '/#jak-to-dziala',
    active: false
  },
])
</script>

<template>
  <UHeader>
    <template #title>
      <img src="/images/logo.png" alt="klisza.app" class="h-8" />
    </template>

    <UNavigationMenu :items="items" class="hidden lg:flex" />

    <template #right>
      <UButton
        label="Panel organizatora"
        variant="soft"
        color="neutral"
        :to="panelUrl"
      />
    </template>

    <template #body>
      <UNavigationMenu :items="items" orientation="vertical" class="-mx-2.5" />
    </template>
  </UHeader>
</template>
