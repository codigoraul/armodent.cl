import type { Ajustes } from '../data/fallback';
const digits = (s: string) => s.replace(/\D/g, '');
export const waLink = (a: Ajustes) => (a.whatsapp ? `https://wa.me/${digits(a.whatsapp)}` : '');
export const telLink = (a: Ajustes) => (a.telefono ? `tel:${digits(a.telefono)}` : '');
