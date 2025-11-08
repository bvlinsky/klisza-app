<template>
  <UApp :toaster="{ position: 'top-center' }">
    <NuxtPage />
  </UApp>
</template>

<script lang="ts" setup>
useHead({
  meta: [
    { name: 'viewport', content: 'width=device-width, initial-scale=1' }
  ],
  link: [
    { rel: 'icon', href: '/favicon.ico' }
  ],
  htmlAttrs: {
    lang: 'pl',
    class: 'dark',
  }
})
</script>