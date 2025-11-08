<template>
  <UApp :toaster="{ position: 'top-center' }">
    <NuxtPage />
  </UApp>
</template>

<script lang="ts" setup>
useHead({
  title: 'klisza.app — Zbieraj zdjęcia z wesela',
  meta: [
    { name: 'viewport', content: 'width=device-width, initial-scale=1' },
    { name: 'description', content: 'Aplikacja do zdjęć weselnych dla gości - prosto i bez instalacji, w retro stylu.' },
    { name: 'theme-color', content: '#000000' },
    // Open Graph meta tags
    { property: 'og:title', content: 'klisza.app — Zbieraj zdjęcia z wesela' },
    { property: 'og:description', content: 'Aplikacja do zdjęć weselnych dla gości - prosto i bez instalacji, w retro stylu.' },
    { property: 'og:image', content: '/images/og.png' },
    { property: 'og:image:width', content: '1200' },
    { property: 'og:image:height', content: '630' },
    { property: 'og:type', content: 'website' },
    { property: 'og:url', content: 'https://klisza.app' },
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