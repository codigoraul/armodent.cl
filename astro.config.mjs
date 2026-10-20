import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

export default defineConfig({
  site: 'https://armodent.cl',
  base: process.env.DEPLOY_BASE || '/',
  output: 'static',
  integrations: [sitemap()],
  build: { inlineStylesheets: 'auto' },
  image: { layout: 'constrained' },
});
