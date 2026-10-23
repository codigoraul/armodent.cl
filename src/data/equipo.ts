// Profesionales de ARMODENT. Para sumar a alguien, agrega un objeto a la lista (el orden es el de la página).
// foto: nombre de archivo dentro de src/assets/equipo/ (ej: 'constanza-figueroa.jpg'); si queda vacío se muestran sus iniciales.
export interface Profesional {
  nombre: string;
  cargo: string;      // ej: "Directora de la Clínica" o "Odontóloga"
  areas?: string[];   // especialidades, se muestran como etiquetas
  bio: string;        // una o dos frases
  foto?: string;      // nombre de archivo dentro de src/assets/equipo/
}

export const equipo: Profesional[] = [
  {
    nombre: 'Constanza Figueroa Cisterna',
    cargo: 'Directora de la Clínica',
    areas: ['Odontología general', 'Implantología', 'Medicina estética facial'],
    bio: 'Odontóloga de la Universidad Mayor. Dirige ARMODENT y trabaja en odontología general, implantología y medicina estética facial.',
    // foto: 'constanza-figueroa.jpg',   // pendiente: foto vertical sobre fondo neutro
  },
];
