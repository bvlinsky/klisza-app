// https://nuxt.com/docs/api/configuration/nuxt-config
declare const defineNuxtConfig: (config: any) => any
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: ['@nuxt/ui', '@pinia/nuxt'],
  css: [new URL('./assets/css/main.css', import.meta.url).pathname],
  ssr: false,
  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL || 'http://localhost',
      stripeOption1Url: process.env.NUXT_PUBLIC_STRIPE_OPTION1_URL || '',
      stripeOption2Url: process.env.NUXT_PUBLIC_STRIPE_OPTION2_URL || '',
      stripeOption3Url: process.env.NUXT_PUBLIC_STRIPE_OPTION3_URL || '',
    },
  },
  ui: {
    colorMode: false
  },
})