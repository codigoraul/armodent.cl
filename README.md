# Armodent · armodent.cl (Astro + WordPress headless)

Sitio web en Astro 5 (estático) con paleta "Rosa y calidez", títulos en **Big Caslon** y texto en Jost.

## Páginas
- `/` Inicio · `/la-clinica/` · `/servicios/` (con 12 páginas `/servicios/<nombre>/`) · `/tu-primera-visita/` · `/contacto/`
- El menú muestra un submenú con todos los servicios. Los textos de cada servicio están en `src/data/fallback.ts`.
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
2. Crea **Servicios**: el título, el extracto (texto de la tarjeta), la imagen destacada y el orden salen de los campos normales; el contenido del editor es el texto "En qué consiste". Campos personalizados: `categoria` (`odontologia`, `ortodoncia` o `estetica`), `lead`, `cuando` (una frase por línea), `proceso` (`Título | Descripción`, una línea por paso) y `faqs` (`Pregunta | Respuesta`, una por línea). Lo que quede vacío se completa con el texto de `src/data/fallback.ts` si el slug coincide. También hay **Preguntas frecuentes** para la landing.
   Cada servicio genera su página en `/servicios/<slug>/`.
3. Completa **Ajustes > Armodent** (dirección, teléfono, WhatsApp, correo que recibe consultas, horario, redes).
4. Pon la URL de WordPress en `PUBLIC_WP_URL` y reconstruye (`npm run build`). Si WP no responde, el sitio usa `src/data/fallback.ts`.
5. El formulario envía a `/wp-json/armodent/v1/contacto` (honeypot + límite por IP + `wp_mail`). Ajusta los dominios permitidos (CORS) al final del plugin.

## Tipografía
Big Caslon es fuente de sistema en macOS/iOS. En Windows/Android se muestra **Libre Caslon Text** (incluida, autoalojada). Si tienes licencia web de Big Caslon, agrega el `@font-face` en `src/styles/global.css` y quedará primera en `--f-titulo`.

## Imágenes provisorias
Las fotos de `src/assets/` (excepto la recepción, que es la foto real de la clínica) se recortaron de la landing de muestra y son referenciales: reemplázalas por fotos propias o con licencia manteniendo los mismos nombres.
