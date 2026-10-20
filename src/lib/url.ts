// Prefija las rutas internas con la base del sitio: "/" normalmente, "/prueba/" en el deploy de prueba (DEPLOY_BASE).
const B = import.meta.env.BASE_URL.replace(/\/$/, '');
export const u = (path = '/') => `${B}${path}`;
