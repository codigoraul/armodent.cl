# Armodent · armodent.cl (Astro + WordPress headless)

Sitio web en Astro 5 (estático) con paleta "Rosa y calidez", títulos en **Big Caslon** y texto en Jost.

## Páginas
- `/` Inicio · `/la-clinica/` · `/servicios/` (listado simple de 10 especialidades, cada una con su página `/servicios/<nombre>/`) · `/tu-primera-visita/` · `/contacto/`
- El menú muestra un submenú con todas las especialidades. Los textos de cada servicio están en `src/data/fallback.ts`.
- Se genera `sitemap-index.xml` y `robots.txt` automáticamente.

## Uso
```bash
npm install
cp .env.example .env     # define PUBLIC_WP_URL (déjalo vacío para usar el contenido por defecto)
npm run dev              # http://localhost:4321
npm run build            # genera /dist
```

## WordPress headless
1. Copia `wordpress/armodent-headless.php` a `wp-content/plugins/armodent-headless/` y actívalo.
2. Los **Servicios** (textos, orden y nombres) se editan en `src/data/fallback.ts`. En WordPress, si creas un servicio con el mismo slug y le pones imagen destacada, el sitio usa esa imagen. También hay **Preguntas frecuentes** para la landing.
   Cada servicio genera su página en `/servicios/<slug>/`.
3. Completa **Ajustes > Armodent** (dirección, teléfono, WhatsApp, correo que recibe consultas, horario, redes).
4. Pon la URL de WordPress en `PUBLIC_WP_URL` y reconstruye (`npm run build`). Si WP no responde, el sitio usa `src/data/fallback.ts`.
5. El formulario envía a `/wp-json/armodent/v1/contacto` (honeypot + límite por IP + `wp_mail`). Ajusta los dominios permitidos (CORS) al final del plugin.

## Tipografía
Big Caslon es fuente de sistema en macOS/iOS. En Windows/Android se muestra **Libre Caslon Text** (incluida, autoalojada). Si tienes licencia web de Big Caslon, agrega el `@font-face` en `src/styles/global.css` y quedará primera en `--f-titulo`.

## Imágenes provisorias
Las fotos de `src/assets/` (excepto la recepción, que es la foto real de la clínica) se recortaron de la landing de muestra y son referenciales: reemplázalas por fotos propias o con licencia manteniendo los mismos nombres.
