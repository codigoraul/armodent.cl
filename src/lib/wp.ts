import { servicios as fbServicios, faqs as fbFaqs, ajustes as fbAjustes } from '../data/fallback';
import type { Servicio, Faq, Ajustes, Paso } from '../data/fallback';

const WP = (import.meta.env.PUBLIC_WP_URL ?? '').replace(/\/$/, '');
export const wpEnabled = WP.length > 0;
export const contactEndpoint = wpEnabled ? `${WP}/wp-json/armodent/v1/contacto` : '';

// Cada consulta a WordPress se hace una sola vez por build.
const memo = <T>(fn: () => Promise<T>) => { let p: Promise<T> | undefined; return () => (p ??= fn()); };

async function wp<T>(path: string): Promise<T | null> {
  if (!wpEnabled) return null;
  try {
    const res = await fetch(`${WP}/wp-json${path}`, { headers: { Accept: 'application/json' } });
    if (!res.ok) return null;
    return (await res.json()) as T;
  } catch {
    console.warn(`[wp] No se pudo leer ${path}; se usa contenido por defecto.`);
    return null;
  }
}

const strip = (html: string) => html.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
const decode = (s: string) =>
  s.replace(/&#8217;/g, '’').replace(/&#8211;/g, '–').replace(/&amp;/g, '&').replace(/&#038;/g, '&').replace(/&hellip;/g, '…');
const lines = (v: unknown) => String(v ?? '').split('\n').map((x) => x.trim()).filter(Boolean);
const pairs = (v: unknown) => lines(v).map((l) => { const [a, ...b] = l.split('|'); return [a.trim(), b.join('|').trim()] as const; }).filter(([a, b]) => a && b);

// Los textos y nombres de los servicios viven en src/data/fallback.ts (validados con el cliente).
// WordPress aporta el ORDEN (campo "Orden" / arrastrar en la lista) y la imagen destacada.
// Algunos servicios tienen en WordPress un slug distinto al del sitio: se aceptan ambos.
const SLUGS_WP: Record<string, string[]> = {
  'estetica-facial': ['medicina-estetica'],
  'ttm-dolor-orofacial': ['ttm-trastornos-temporomandibulares'],
};

export const getServicios = memo(async (): Promise<Servicio[]> => {
  const data = await wp<any[]>('/wp/v2/servicio?per_page=50&orderby=menu_order&order=asc&_embed=wp:featuredmedia');
  const wpPorSlug = new Map<string, any>((data ?? []).map((p) => [p.slug, p]));
  return fbServicios
    .map((s) => {
      const p = [s.slug, ...(SLUGS_WP[s.slug] ?? [])].map((k) => wpPorSlug.get(k)).find(Boolean);
      return {
        ...s,
        image: p?._embedded?.['wp:featuredmedia']?.[0]?.source_url || undefined,
        // Sin equivalente en WordPress: al final (no adelante)
        orden: p ? Number(p.menu_order) || 0 : Number.MAX_SAFE_INTEGER,
      };
    })
    // Orden definido en WordPress, de menor a mayor; empates en orden alfabético.
    .sort((a, b) => a.orden - b.orden || a.title.localeCompare(b.title, 'es', { sensitivity: 'base' }))
    .map(({ orden, ...s }) => s);
});

export const getFaqs = memo(async (): Promise<Faq[]> => {
  const data = await wp<any[]>('/wp/v2/faq?per_page=50&orderby=menu_order&order=asc');
  if (!data?.length) return fbFaqs;
  return data.map((p) => ({ q: decode(strip(p.title?.rendered ?? '')), a: p.content?.rendered ?? '' }));
});

export const getAjustes = memo(async (): Promise<Ajustes> => {
  const data = await wp<Partial<Ajustes>>('/armodent/v1/ajustes');
  // Solo pisa los datos por defecto con lo que WordPress tenga realmente completado.
  const llenos = Object.fromEntries(Object.entries(data ?? {}).filter(([, v]) => typeof v === 'string' && v.trim() !== ''));
  return { ...fbAjustes, ...llenos };
});
