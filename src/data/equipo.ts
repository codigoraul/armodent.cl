// Profesionales de ARMODENT. COMPLETAR con los datos reales de cada persona.
// foto: importa la imagen en la página (src/assets/equipo/nombre.jpg) o deja vacío para mostrar sus iniciales.
export interface Profesional {
  nombre: string;
  cargo: string;      // ej: "Odontóloga · Ortodoncia"
  bio: string;        // una o dos frases
  foto?: string;      // nombre de archivo dentro de src/assets/equipo/ (ej: 'maria-perez.jpg')
}

export const equipo: Profesional[] = [
  { nombre: 'Nombre y apellido', cargo: 'Cargo o especialidad', bio: 'Breve presentación de la profesional: formación, áreas de trabajo y su forma de atender.' },
  { nombre: 'Nombre y apellido', cargo: 'Cargo o especialidad', bio: 'Breve presentación del profesional: formación, áreas de trabajo y su forma de atender.' },
  { nombre: 'Nombre y apellido', cargo: 'Cargo o especialidad', bio: 'Breve presentación del profesional: formación, áreas de trabajo y su forma de atender.' },
];
