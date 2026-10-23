// Contenido por defecto. WordPress (si está conectado) lo reemplaza servicio por servicio.
export interface Paso { t: string; d: string }
export interface Faq { q: string; a: string }
export interface Servicio {
  slug: string;
  title: string;
  short?: string;       // nombre corto para menú, ticker y pie de página
  excerpt: string;      // tarjeta y meta description
  lead: string;         // bajada de la página de detalle
  que: string[];        // párrafos "En qué consiste"
  cuando: string[];     // "Cuándo conviene consultar"
  proceso: Paso[];      // "Cómo trabajamos" (3 pasos)
  faqs: Faq[];
  html?: string;        // si viene de WordPress, reemplaza a `que`
  image?: string;       // URL remota (imagen destacada en WordPress)
}
export interface Ajustes {
  direccion: string; telefono: string; whatsapp: string; email: string; horario: string;
  instagram: string; facebook: string;
}

export const servicios: Servicio[] = [
  {
    slug: 'odontologia-general',
    title: 'Odontología General',
    excerpt: 'Prevención, diagnóstico y tratamiento de enfermedades bucodentales, promoviendo una sonrisa saludable y funcional.',
    lead: 'El punto de partida para cuidar tu boca: una revisión completa que permite detectar a tiempo y decidir con calma.',
    que: [
      'La odontología general abarca el cuidado preventivo y el tratamiento de las necesidades más frecuentes de dientes y encías: revisiones periódicas, limpiezas, control de caries y restauraciones, entre otras.',
      'En ARMODENT partimos por escucharte y por conocer tu historia. Con esa información y una valoración clínica, te explicamos qué encontramos, qué conviene hacer primero y qué puede esperar.',
    ],
    cuando: [
      'Hace tiempo que no te haces una revisión dental.',
      'Sientes sensibilidad, molestia o dolor al masticar o con el frío y el calor.',
      'Notas encías que sangran o cambios en el color o la forma de tus dientes.',
      'Quieres un plan de cuidado para ti o para tu familia.',
    ],
    proceso: [
      { t: 'Escuchamos tu motivo de consulta', d: 'Conversamos sobre tus molestias, tus antecedentes y lo que esperas de la visita.' },
      { t: 'Revisamos tu salud oral', d: 'Realizamos una valoración clínica y, si hace falta, solicitamos estudios complementarios.' },
      { t: 'Acordamos los pasos', d: 'Te explicamos las opciones, su orden y los cuidados para que decidas con claridad.' },
    ],
    faqs: [
      { q: '¿Cada cuánto conviene una revisión?', a: 'No hay una única respuesta: depende de tu salud oral y de tus antecedentes. En tu valoración te indicamos una frecuencia adecuada para ti.' },
      { q: '¿Una limpieza duele?', a: 'Lo habitual es que sea bien tolerada. Si tienes sensibilidad, cuéntanoslo antes de empezar para cuidar tu comodidad.' },
    ],
  },
  {
    slug: 'implantologia-bucomaxilofacial',
    title: 'Implantología Bucomaxilofacial',
    excerpt: 'Reemplazo de dientes perdidos mediante implantes dentales, recuperando la función masticatoria, estética y comodidad.',
    lead: 'Una alternativa para reponer dientes ausentes, planificada con estudios y una mirada integral.',
    que: [
      'Un implante dental es una pieza que se coloca en el hueso de los maxilares para reemplazar la raíz de un diente ausente y sostener sobre ella una corona, un puente o una prótesis.',
      'No todas las personas parten de las mismas condiciones. Evaluamos el estado de encías y hueso mediante estudios e imágenes, y te explicamos si el implante es una opción, si se necesitan procedimientos previos y cuál sería el plan.',
    ],
    cuando: [
      'Perdiste un diente o varios y quieres reponerlos.',
      'Usas una prótesis que se mueve o te incomoda.',
      'Quieres conocer alternativas para reponer un diente sin desgastar los vecinos.',
      'Te indicaron una extracción y quieres planificar con tiempo.',
    ],
    proceso: [
      { t: 'Valoración e imágenes', d: 'Revisamos tu boca y solicitamos estudios para conocer el estado de encías y hueso.' },
      { t: 'Planificación', d: 'Te presentamos el plan, las etapas y los cuidados previos y posteriores.' },
      { t: 'Cirugía, rehabilitación y control', d: 'Colocamos el implante, restauramos la pieza y hacemos seguimiento.' },
    ],
    faqs: [
      { q: '¿Todas las personas pueden recibir implantes?', a: 'No siempre. Depende de tu salud general, de tus encías y del hueso disponible; se define tras una evaluación individual.' },
      { q: '¿El procedimiento es doloroso?', a: 'Se realiza con anestesia. Después puede haber molestias, que se controlan con las indicaciones que te daremos.' },
    ],
  },
  {
    slug: 'cirugia-bucomaxilofacial',
    title: 'Cirugía Bucomaxilofacial',
    excerpt: 'Diagnóstico y tratamiento quirúrgico de alteraciones de la cavidad oral y estructuras maxilofaciales, incluyendo extracciones complejas y terceros molares.',
    lead: 'Atención quirúrgica especializada de la boca, los maxilares y las estructuras de la cara.',
    que: [
      'La cirugía bucomaxilofacial aborda condiciones que afectan a la boca, los maxilares y zonas de la cara. Puede incluir, por ejemplo, la extracción de muelas del juicio u otras piezas retenidas, el tratamiento de quistes o la cirugía preparatoria para prótesis o implantes.',
      'Toda cirugía parte de una evaluación y de estudios de imagen. Te explicamos el diagnóstico, en qué consiste el procedimiento, sus riesgos y los cuidados antes y después, para que tomes una decisión informada.',
    ],
    cuando: [
      'Te indicaron extraer muelas del juicio u otras piezas retenidas.',
      'Tienes una inflamación, un quiste o una lesión en la boca que requiere evaluación.',
      'Necesitas preparar el hueso o los tejidos antes de colocar implantes.',
      'Quieres una segunda opinión sobre un procedimiento quirúrgico.',
    ],
    proceso: [
      { t: 'Evaluación y estudios', d: 'Revisamos tu caso y solicitamos las imágenes y exámenes necesarios.' },
      { t: 'Decisión informada', d: 'Te explicamos riesgos, alternativas y cuidados, y resolvemos tus dudas antes de decidir.' },
      { t: 'Cirugía y control', d: 'Realizamos el procedimiento con las medidas de cuidado necesarias y controlamos tu recuperación.' },
    ],
    faqs: [
      { q: '¿Necesitaré reposo después?', a: 'Depende del procedimiento. Antes de la cirugía te explicamos qué esperar y cómo cuidarte.' },
      { q: '¿Puedo consultar antes de decidir una cirugía?', a: 'Sí. La consulta sirve para evaluar tu caso y resolver dudas, sin que implique decidir de inmediato.' },
    ],
  },
  {
    slug: 'endodoncia',
    title: 'Endodoncia',
    excerpt: 'Tratamiento de enfermedades que afectan la pulpa dental, permitiendo conservar dientes naturales y aliviar el dolor.',
    lead: 'Cuando el interior del diente se inflama o se infecta, la endodoncia puede ser una forma de conservarlo.',
    que: [
      'La endodoncia, conocida como tratamiento de conducto, consiste en limpiar y desinfectar el interior de un diente cuya pulpa está dañada o infectada, y sellarlo para ayudar a conservarlo.',
      'No todo dolor dental requiere una endodoncia. Primero hacemos una valoración y estudios para entender qué ocurre, y te explicamos si es el camino indicado, qué esperar y qué cuidados seguir.',
    ],
    cuando: [
      'Tienes dolor intenso o que te despierta por la noche.',
      'Sientes sensibilidad prolongada al frío o al calor.',
      'Notas inflamación, un grano en la encía o un diente que cambió de color.',
      'Un golpe o una caries profunda comprometió una pieza.',
    ],
    proceso: [
      { t: 'Valoramos el diente', d: 'Evaluamos tus síntomas y realizamos estudios, como radiografías, para llegar a un diagnóstico.' },
      { t: 'Te explicamos el tratamiento', d: 'Conversamos sobre las opciones, las etapas y la restauración posterior del diente.' },
      { t: 'Tratamos y hacemos seguimiento', d: 'Realizamos el procedimiento y controlamos cómo evoluciona.' },
    ],
    faqs: [
      { q: '¿Duele una endodoncia?', a: 'Se realiza con anestesia para cuidar tu comodidad. Es posible sentir alguna molestia después; te daremos indicaciones para sobrellevarla.' },
      { q: '¿Qué pasa si no trato un diente con infección?', a: 'La infección puede avanzar y causar más dolor o complicaciones. Por eso conviene consultar pronto para evaluar tu caso.' },
    ],
  },
  {
    slug: 'ortodoncia',
    title: 'Ortodoncia',
    excerpt: 'Corrección de la posición dental y alteraciones de la mordida mediante tratamientos convencionales y alineadores transparentes.',
    lead: 'Una sonrisa alineada no es solo estética: también puede ayudar a cómo muerdes y a cómo limpias tus dientes.',
    que: [
      'La ortodoncia corrige la posición de los dientes y la relación entre las arcadas dentales. Puede abordar apiñamiento, espacios, mordidas cruzadas o abiertas y otras alteraciones, y se planifica según un diagnóstico individual.',
      'Según tu caso, el tratamiento puede ser convencional o con alineadores transparentes y removibles, una alternativa discreta que mueve los dientes de forma progresiva y que puedes retirar para comer y cepillarte. No es adecuada para todos los casos: en la valoración evaluamos qué opción responde mejor a tu diagnóstico y qué exige de tu parte, como constancia en el uso indicado y asistencia a los controles.',
      'Estudiamos tu caso, te explicamos las alternativas posibles y acordamos juntos un plan, con controles periódicos para acompañar cada etapa.',
    ],
    cuando: [
      'Tus dientes están apiñados, separados o montados.',
      'Sientes que tu mordida no cierra bien o te cuesta masticar.',
      'Quieres orientación sobre ortodoncia para ti o para tu hijo o hija.',
      'La posición de tus dientes dificulta la higiene en algunas zonas.',
      'Prefieres una opción discreta, como los alineadores transparentes, y quieres saber si es adecuada para ti.',
    ],
    proceso: [
      { t: 'Diagnóstico', d: 'Revisamos tu boca y solicitamos los estudios necesarios.' },
      { t: 'Plan a tu medida', d: 'Te presentamos las alternativas, sus etapas y los cuidados que implican.' },
      { t: 'Controles periódicos', d: 'Ajustamos y monitoreamos el avance, y al terminar conversamos sobre la retención.' },
    ],
    faqs: [
      { q: '¿Hay una edad para comenzar?', a: 'Puede indicarse a distintas edades. Lo importante es una evaluación, que nos dirá si conviene comenzar ahora o esperar.' },
      { q: '¿Se nota que llevo alineadores transparentes?', a: 'Son transparentes, por lo que resultan discretos, aunque pueden notarse de cerca. En la valoración te decimos si son una opción adecuada para tu caso.' },
      { q: '¿Cuánto dura un tratamiento?', a: 'Depende de cada caso. Tras el diagnóstico te explicamos una estimación y los factores que pueden influir.' },
    ],
  },
  {
    slug: 'periodoncia',
    title: 'Periodoncia',
    excerpt: 'Prevención y tratamiento de enfermedades de las encías y tejidos que sostienen los dientes, como gingivitis y periodontitis.',
    lead: 'Unas encías sanas son la base de una sonrisa que dure en el tiempo.',
    que: [
      'La periodoncia es la especialidad que se ocupa de las encías y del hueso que sostiene los dientes. Su foco es prevenir, diagnosticar y tratar la gingivitis y la periodontitis, además de acompañar el mantenimiento a largo plazo.',
      'Muchas veces los cambios en las encías avanzan sin dolor. Por eso una evaluación periodontal permite detectarlos a tiempo y definir un plan de cuidado y controles acorde a tu caso.',
    ],
    cuando: [
      'Tus encías sangran al cepillarte o al usar hilo dental.',
      'Notas encías enrojecidas, inflamadas o que se han retraído.',
      'Tienes mal aliento persistente o dientes que sientes móviles.',
      'Hay antecedentes de enfermedad de las encías en tu familia.',
    ],
    proceso: [
      { t: 'Evaluamos tus encías', d: 'Revisamos el estado de encías y soporte, con estudios cuando corresponde.' },
      { t: 'Te explicamos el diagnóstico', d: 'Te mostramos qué encontramos y qué significa para tu salud oral.' },
      { t: 'Planificamos tratamiento y controles', d: 'Definimos los pasos, tus cuidados en casa y la frecuencia de seguimiento.' },
    ],
    faqs: [
      { q: '¿Es normal que sangren las encías al cepillarme?', a: 'Es una señal que conviene evaluar, porque puede indicar inflamación. Con una revisión podemos orientarte.' },
      { q: '¿La enfermedad de las encías tiene solución?', a: 'Depende de la etapa y de cada caso. Cuanto antes se detecta, más opciones existen; el control y el cuidado diario son parte clave del tratamiento.' },
    ],
  },
  {
    slug: 'rehabilitacion-oral',
    title: 'Rehabilitación Oral',
    excerpt: 'Recuperación de la función, estética y armonía de la sonrisa mediante coronas, carillas, prótesis y otras restauraciones dentales.',
    lead: 'Devolver función y armonía a tu boca, con un plan pensado en conjunto.',
    que: [
      'La rehabilitación oral busca reponer o reconstruir dientes y estructuras dañadas o perdidas, para recuperar la masticación, el habla y la estética. Puede incluir coronas, puentes, prótesis, restauraciones o una combinación de tratamientos.',
      'Cada caso se planifica de forma integral: evaluamos tu boca completa, tus necesidades y tus expectativas, y te presentamos las alternativas con sus etapas y cuidados.',
    ],
    cuando: [
      'Te faltan uno o más dientes.',
      'Tienes dientes muy desgastados, fracturados o con restauraciones antiguas.',
      'Te cuesta masticar con comodidad.',
      'Quieres recuperar la armonía de tu sonrisa tras años de desgaste.',
    ],
    proceso: [
      { t: 'Evaluación integral', d: 'Revisamos dientes, encías y mordida, con los estudios necesarios.' },
      { t: 'Plan por etapas', d: 'Ordenamos los tratamientos y te explicamos tiempos, materiales y cuidados.' },
      { t: 'Rehabilitación y mantención', d: 'Realizamos el plan y acordamos controles para cuidar el resultado.' },
    ],
    faqs: [
      { q: '¿Se hace todo en una sola sesión?', a: 'Generalmente se hace por etapas. El plan y su duración dependen de tu caso y se explican tras la valoración.' },
      { q: '¿Qué cuidados requiere después?', a: 'Una higiene adecuada y controles periódicos ayudan a que los trabajos duren más. Te damos indicaciones según tu rehabilitación.' },
    ],
  },
  {
    slug: 'odontopediatria',
    title: 'Odontopediatría',
    excerpt: 'Atención odontológica especializada en bebés, niños y adolescentes, enfocada en la prevención y el desarrollo de una salud bucal adecuada.',
    lead: 'Cuidar la salud oral desde pequeños y acompañar a tu hijo o hija con calma y confianza.',
    que: [
      'La odontopediatría se enfoca en la salud oral de bebés, niños y adolescentes: revisiones, prevención, orientación sobre higiene y alimentación, y tratamiento de las necesidades propias de cada etapa de crecimiento.',
      'Cuidamos el ritmo de la visita. Explicamos cada paso con palabras simples para que niñas y niños se sientan seguros, y orientamos a madres, padres y cuidadores.',
    ],
    cuando: [
      'Quieres hacer la primera visita de tu hijo o hija.',
      'Notas manchas, caries o molestias en dientes de leche o definitivos.',
      'Tu hijo o hija se chupa el dedo, usa chupete o respira por la boca.',
      'Se acerca el cambio de dientes y quieres orientación.',
    ],
    proceso: [
      { t: 'Conocemos a tu hijo o hija', d: 'Partimos con una conversación tranquila, sin apuro y con los adultos presentes.' },
      { t: 'Revisamos con cuidado', d: 'Hacemos una valoración adaptada a su edad y a cómo se sienta ese día.' },
      { t: 'Acordamos un plan', d: 'Te explicamos lo que encontramos, los cuidados en casa y los siguientes pasos.' },
    ],
    faqs: [
      { q: '¿Desde qué edad conviene traerlos?', a: 'Se recomienda una primera visita temprana, cuando aparecen los primeros dientes. En la consulta te orientamos según su edad.' },
      { q: '¿Puedo acompañar a mi hijo o hija durante la atención?', a: 'Lo conversamos antes de comenzar. La idea es que se sienta seguro, y definimos juntos la mejor forma de acompañarlo.' },
    ],
  },
  {
    slug: 'ttm-dolor-orofacial',
    title: 'Trastornos Temporomandibulares (TTM) y Dolor Orofacial',
    short: 'TTM y dolor orofacial',
    excerpt: 'Diagnóstico y tratamiento de alteraciones de la articulación temporomandibular, musculatura masticatoria y dolores asociados.',
    lead: 'Cuando la mandíbula o el rostro duelen, suenan o se traban, vale la pena evaluar la causa.',
    que: [
      'Los trastornos temporomandibulares (TTM) afectan a la articulación que une la mandíbula con el cráneo y a los músculos que intervienen al masticar. Junto con otros dolores del rostro y la boca (dolor orofacial), pueden manifestarse con dolor, ruidos, limitación al abrir la boca o dolores de cabeza.',
      'Sus causas son variadas, por eso partimos con una evaluación detallada de tu historia, tu mordida y tu función mandibular. Según el diagnóstico, te proponemos un plan de manejo y, si es necesario, trabajamos junto a otros profesionales.',
    ],
    cuando: [
      'Sientes dolor en la mandíbula, el oído o las sienes al masticar o hablar.',
      'Tu mandíbula hace ruidos, se traba o se desvía al abrir la boca.',
      'Aprietas o rechinas los dientes, o despiertas con la mandíbula cansada.',
      'Tienes dolores de cabeza frecuentes sin una causa clara.',
    ],
    proceso: [
      { t: 'Historia y evaluación', d: 'Conversamos sobre tus síntomas y evaluamos articulación, músculos y mordida.' },
      { t: 'Diagnóstico y plan', d: 'Te explicamos qué encontramos y cuáles son las opciones de manejo.' },
      { t: 'Seguimiento', d: 'Controlamos tu evolución y ajustamos el plan según tu respuesta.' },
    ],
    faqs: [
      { q: '¿Los ruidos al abrir la boca son siempre un problema?', a: 'No necesariamente. Si no duelen ni limitan el movimiento pueden no requerir tratamiento, pero conviene evaluarlos.' },
      { q: '¿Se puede tratar sin cirugía?', a: 'En muchos casos el manejo es conservador. Cada caso se define tras una evaluación individual.' },
    ],
  },
  {
    slug: 'estetica-facial',
    title: 'Estética Facial y Armonización Orofacial',
    short: 'Estética facial',
    excerpt: 'Tratamientos mínimamente invasivos orientados a realzar la belleza natural, mejorar la armonía del rostro y atenuar los signos del envejecimiento.',
    lead: 'Realzar tu expresión con naturalidad, sin perder tu esencia.',
    que: [
      'La estética facial y la armonización orofacial reúnen tratamientos mínimamente invasivos orientados a realzar la belleza natural y cuidar la armonía del rostro. En ARMODENT se integran a una mirada de salud oral y facial, y comienzan siempre con una valoración personalizada.',
      'Consideramos tus rasgos, tus expectativas y tu bienestar. Te explicamos qué es posible, cuáles son los alcances y limitaciones y qué cuidados requiere, sin prometer resultados idénticos entre personas.',
    ],
    cuando: [
      'Quieres conversar sobre el cuidado y la armonía de tu rostro.',
      'Buscas orientación antes de decidir un procedimiento.',
      'Prefieres una mirada que combine salud oral y estética facial.',
      'Tienes dudas sobre qué es adecuado para ti.',
    ],
    proceso: [
      { t: 'Conversamos', d: 'Escuchamos qué te gustaría cuidar o cambiar, y por qué.' },
      { t: 'Valoramos tu rostro', d: 'Realizamos una evaluación individual y te explicamos las opciones.' },
      { t: 'Decidimos contigo', d: 'Si es adecuado, acordamos un plan con sus cuidados y controles.' },
    ],
    faqs: [
      { q: '¿Los resultados se notan mucho?', a: 'Buscamos naturalidad. El resultado depende de cada persona, y lo conversamos antes de decidir.' },
      { q: '¿Todos los procedimientos son para todas las personas?', a: 'No. Cada procedimiento está sujeto a valoración e indicación profesional.' },
    ],
  },
];

export const faqs: Faq[] = [
  { q: '¿Qué ocurre en la primera visita?', a: 'Revisamos tus inquietudes y antecedentes, realizamos una valoración y conversamos sobre los siguientes pasos. Si se necesitan estudios complementarios, te explicaremos por qué.' },
  { q: '¿Qué tratamiento es adecuado para mí?', a: 'Depende de tu caso. Después de la valoración te presentamos las opciones posibles, sus etapas y cuidados, para que decidas con información clara.' },
  { q: '¿Puedo consultar por odontología y estética facial?', a: 'Sí. Puedes consultar por ambas áreas en una misma conversación; la indicación de cada tratamiento requiere una evaluación clínica individual.' },
  { q: '¿Qué debo llevar a mi cita?', a: 'Si tienes radiografías, informes o exámenes anteriores, tráelos. También puedes anotar tus dudas para que no se nos pase ninguna.' },
];

// Datos de contacto entregados por el cliente. Lo que se complete en WordPress (Ajustes > Armodent) tiene prioridad.
export const ajustes: Ajustes = {
  direccion: 'Av. Ossa 235, Of. 845',
  telefono: '+56 9 3468 9968',
  whatsapp: '+56 9 3468 9968',
  email: 'contacto@armodent.cl',
  horario: 'Lunes a Viernes de 9:30 a 18:30 hrs. · Sábados de 10:00 a 14:00 hrs.',
  instagram: '',   // pendiente: el cliente está creando las cuentas
  facebook: '',
};
