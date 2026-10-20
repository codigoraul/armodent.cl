import odontologia from '../assets/servicio-odontologia.jpg';
import ortodoncia from '../assets/servicio-ortodoncia.jpg';
import estetica from '../assets/servicio-estetica.jpg';
import clinica from '../assets/clinica.jpg';
import sonrisa from '../assets/hero-sonrisa.jpg';
import esteticaFacial from '../assets/estetica-facial.jpg';
import f_odontologia_general from '../assets/servicios/odontologia-general.jpg';
import f_odontopediatria from '../assets/servicios/odontopediatria.jpg';
import f_periodoncia from '../assets/servicios/periodoncia.jpg';
import f_endodoncia from '../assets/servicios/endodoncia.jpg';
import f_ortodoncia from '../assets/servicios/ortodoncia.jpg';
import f_ortodoncia_invisible from '../assets/servicios/ortodoncia-invisible.jpg';
import f_orthosoft from '../assets/servicios/orthosoft.jpg';
import f_rehabilitacion_oral from '../assets/servicios/rehabilitacion-oral.jpg';
import f_implantologia_bucomaxilofacial from '../assets/servicios/implantologia-bucomaxilofacial.jpg';
import f_cirugia_bucomaxilofacial from '../assets/servicios/cirugia-bucomaxilofacial.jpg';
import f_ttm_trastornos_temporomandibulares from '../assets/servicios/ttm-trastornos-temporomandibulares.jpg';
import f_medicina_estetica from '../assets/servicios/medicina-estetica.jpg';
import type { CategoriaId } from '../data/fallback';

// Imagen por categoría. Si un servicio trae imagen destacada desde WordPress, esa tiene prioridad.
export const imagenCategoria: Record<CategoriaId, ImageMetadata> = { odontologia, ortodoncia, estetica };
export const servicioHref = (slug: string) => `/servicios/${slug}/`;

// Foto por servicio (Freepik, licencia gratuita: requiere atribución a Freepik).
type Enc = { src: ImageMetadata; pos: string; zoom?: number };
export const imagenServicio: Record<string, Enc> = {
  'odontologia-general': { src: f_odontologia_general, pos: '50% 40%' },
  'odontopediatria': { src: f_odontopediatria, pos: '50% 40%' },
  'periodoncia': { src: f_periodoncia, pos: '50% 40%' },
  'endodoncia': { src: f_endodoncia, pos: '50% 40%' },
  'ortodoncia': { src: f_ortodoncia, pos: '50% 40%' },
  'ortodoncia-invisible': { src: f_ortodoncia_invisible, pos: '50% 40%' },
  'orthosoft': { src: f_orthosoft, pos: '50% 40%' },
  'rehabilitacion-oral': { src: f_rehabilitacion_oral, pos: '50% 40%' },
  'implantologia-bucomaxilofacial': { src: f_implantologia_bucomaxilofacial, pos: '50% 40%' },
  'cirugia-bucomaxilofacial': { src: f_cirugia_bucomaxilofacial, pos: '50% 40%' },
  'ttm-trastornos-temporomandibulares': { src: f_ttm_trastornos_temporomandibulares, pos: '50% 40%' },
  'medicina-estetica': { src: f_medicina_estetica, pos: '50% 40%' },
};
export const imagenDeServicio = (slug: string, cat: CategoriaId): Enc => imagenServicio[slug] ?? { src: imagenCategoria[cat], pos: '50% 50%' };
