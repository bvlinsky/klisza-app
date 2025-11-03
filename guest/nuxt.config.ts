// https://nuxt.com/docs/api/configuration/nuxt-config
declare const defineNuxtConfig: (config: any) => any
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: ['@nuxt/ui'],
  css: [new URL('./assets/css/main.css', import.meta.url).pathname],
})
