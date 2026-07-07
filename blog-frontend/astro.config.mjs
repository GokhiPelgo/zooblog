// @ts-check
import { defineConfig } from 'astro/config';

// https://astro.build/config
// Tailwind v4 se procesa vía PostCSS (postcss.config.mjs), porque
// @tailwindcss/vite no es compatible con el Vite (rolldown) de Astro 6.
export default defineConfig({
  output: 'static',
  site: process.env.PUBLIC_SITE_URL ?? 'http://localhost:4321',
  i18n: {
    locales: ['es', 'en'],
    defaultLocale: 'es',
    routing: {
      prefixDefaultLocale: true,
    },
  },
});
