import f_odontologia_general from '../assets/servicios/odontologia-general.jpg';
import f_odontopediatria from '../assets/servicios/odontopediatria.jpg';
import f_periodoncia from '../assets/servicios/periodoncia.jpg';
import f_endodoncia from '../assets/servicios/endodoncia.jpg';
import f_ortodoncia from '../assets/servicios/ortodoncia.jpg';
import f_rehabilitacion_oral from '../assets/servicios/rehabilitacion-oral.jpg';
import f_implantologia_bucomaxilofacial from '../assets/servicios/implantologia-bucomaxilofacial.jpg';
import f_cirugia_bucomaxilofacial from '../assets/servicios/cirugia-bucomaxilofacial.jpg';
import f_ttm from '../assets/servicios/ttm-trastornos-temporomandibulares.jpg';
import f_estetica_facial from '../assets/servicios/medicina-estetica.jpg';
import clinica from '../assets/clinica.jpg';
import { u } from './url';

export const servicioHref = (slug: string) => u(`/servicios/${slug}/`);

// Foto por servicio (Freepik, licencia gratuita: requiere atribución a Freepik). Provisorias hasta tener fotos propias.
// Si un servicio trae imagen destacada desde WordPress, esa tiene prioridad.
type Enc = { src: ImageMetadata; pos: string; zoom?: number };
export const imagenServicio: Record<string, Enc> = {
  'odontologia-general': { src: f_odontologia_general, pos: '50% 40%' },
  'implantologia-bucomaxilofacial': { src: f_implantologia_bucomaxilofacial, pos: '50% 40%' },
  'cirugia-bucomaxilofacial': { src: f_cirugia_bucomaxilofacial, pos: '50% 40%' },
  'endodoncia': { src: f_endodoncia, pos: '50% 40%' },
  'ortodoncia': { src: f_ortodoncia, pos: '50% 40%' },
  'periodoncia': { src: f_periodoncia, pos: '50% 40%' },
  'rehabilitacion-oral': { src: f_rehabilitacion_oral, pos: '50% 40%' },
  'odontopediatria': { src: f_odontopediatria, pos: '50% 40%' },
  'ttm-dolor-orofacial': { src: f_ttm, pos: '50% 40%' },
  'estetica-facial': { src: f_estetica_facial, pos: '50% 40%' },
};
export const imagenDeServicio = (slug: string): Enc => imagenServicio[slug] ?? { src: clinica, pos: '50% 50%' };
