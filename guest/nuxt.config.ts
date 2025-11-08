// https://nuxt.com/docs/api/configuration/nuxt-config
declare const defineNuxtConfig: (config: any) => any

const SITE_URL = process.env.NUXT_PUBLIC_SITE_URL || 'https://klisza.app'

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: ['@nuxt/ui', '@pinia/nuxt'],
  css: [new URL('./assets/css/main.css', import.meta.url).pathname],
  ssr: false,
  runtimeConfig: {
    public: {
      siteUrl: SITE_URL,
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL || 'http://localhost',
      stripeOption1Url: process.env.NUXT_PUBLIC_STRIPE_OPTION1_URL || '',
      stripeOption2Url: process.env.NUXT_PUBLIC_STRIPE_OPTION2_URL || '',
      stripeOption3Url: process.env.NUXT_PUBLIC_STRIPE_OPTION3_URL || '',
    },
  },
  app: {
    head: {
      title: 'klisza.app - Zbieraj zdjęcia z wesela',
      meta: [
        { name: 'description', content: 'Aplikacja do zdjęć weselnych dla gości - prosto i bez instalacji, w retro stylu.' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' },
        { name: 'theme-color', content: '#000000' },
        { property: 'og:title', content: 'klisza.app - Zbieraj zdjęcia z wesela' },
        { property: 'og:description', content: 'Aplikacja do zdjęć weselnych dla gości - prosto i bez instalacji, w retro stylu.' },
        { property: 'og:image', content: SITE_URL + '/images/og.png' },
        { property: 'og:image:secure_url', content: SITE_URL + '/images/og.png' },
        { property: 'og:image:width', content: '1200' },
        { property: 'og:image:height', content: '630' },
        { property: 'og:type', content: 'website' },
        { property: 'og:url', content: SITE_URL },
      ],
      link: [
        { rel: 'icon', href: '/favicon.ico' }
      ],
      htmlAttrs: {
        lang: 'pl',
        class: 'dark'
      }
    }
  },
  ui: {
    colorMode: false
  },
})