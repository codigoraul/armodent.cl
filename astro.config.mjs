import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

export default defineConfig({
  site: 'https://armodent.cl',
  output: 'static',
  integrations: [sitemap()],
  build: { inlineStylesheets: 'auto' },
  image: { layout: 'constrained' },
});
