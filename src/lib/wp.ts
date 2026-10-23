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

// Los textos, el orden y los nombres de los servicios viven en src/data/fallback.ts (validados con el cliente).
// WordPress solo aporta, si existe un servicio con el mismo slug, su imagen destacada.
export const getServicios = memo(async (): Promise<Servicio[]> => {
  const data = await wp<any[]>('/wp/v2/servicio?per_page=50&_embed=wp:featuredmedia');
  const imagenes = new Map<string, string>((data ?? []).map((p) => [p.slug, p._embedded?.['wp:featuredmedia']?.[0]?.source_url] as [string, string]));
  return fbServicios.map((s) => ({ ...s, image: imagenes.get(s.slug) || undefined }));
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
