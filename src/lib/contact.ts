import type { Ajustes } from '../data/fallback';
const digits = (s: string) => s.replace(/\D/g, '');
export const waLink = (a: Ajustes) => (a.whatsapp ? `https://wa.me/${digits(a.whatsapp)}` : '');
export const telLink = (a: Ajustes) => (a.telefono ? `tel:${digits(a.telefono)}` : '');
// Botón de agenda online: abre el WhatsApp de la clínica con un mensaje listo para pedir hora.
export const agendaLink = (a: Ajustes, fallback: string) =>
  a.whatsapp ? `https://wa.me/${digits(a.whatsapp)}?text=${encodeURIComponent('Hola, quisiera agendar una hora en ARMODENT.')}` : fallback;
export const agendaExterna = (a: Ajustes) => (a.whatsapp ? { target: '_blank', rel: 'noopener' } : {});
// Mapa de Google (sin API key) y enlace "Cómo llegar" a partir de la dirección.
export const mapaQuery = (a: Ajustes) => encodeURIComponent(`${a.direccion.replace(/,?\s*(Of\.?|Oficina)\s*\d+/i, '')}, Chile`);
