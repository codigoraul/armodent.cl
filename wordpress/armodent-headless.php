<?php
/**
 * Plugin Name: Armodent Headless
 * Description: Tipos de contenido (Servicios, Preguntas frecuentes), ajustes de contacto y endpoint del formulario para el sitio Astro de armodent.cl.
 * Version: 1.0.0
 * Text Domain: armodent
 */
if (!defined('ABSPATH')) exit;

/* ---------- Tipos de contenido ---------- */
add_action('init', function () {
  register_post_type('servicio', [
    'labels' => ['name' => 'Servicios', 'singular_name' => 'Servicio', 'add_new_item' => 'Añadir servicio'],
    'public' => true, 'publicly_queryable' => false, 'show_in_rest' => true, 'menu_icon' => 'dashicons-heart',
    'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'custom-fields'],
  ]);
  register_post_type('faq', [
    'labels' => ['name' => 'Preguntas frecuentes', 'singular_name' => 'Pregunta', 'add_new_item' => 'Añadir pregunta'],
    'public' => true, 'publicly_queryable' => false, 'show_in_rest' => true, 'menu_icon' => 'dashicons-editor-help',
    'supports' => ['title', 'editor', 'page-attributes'],
  ]);
  $campos = [
    'lead'      => 'Bajada de la página (una o dos frases)',
    'cuando'    => 'Cuándo consultar: una frase por línea',
    'proceso'   => 'Cómo trabajamos: una línea por paso, con formato Título | Descripción',
    'faqs'      => 'Preguntas: una línea por pregunta, con formato Pregunta | Respuesta',
  ];
  foreach ($campos as $key => $desc) {
    register_post_meta('servicio', $key, ['type' => 'string', 'single' => true, 'show_in_rest' => true, 'description' => $desc,
      'auth_callback' => fn() => current_user_can('edit_posts')]);
  }
});

/* ---------- Ajustes de contacto (Ajustes > Armodent) ---------- */
const ARMODENT_FIELDS = [
  'direccion' => 'Dirección', 'telefono' => 'Teléfono', 'whatsapp' => 'WhatsApp (con código de país)',
  'email' => 'Correo que recibe las consultas', 'horario' => 'Horario de atención', 'instagram' => 'URL de Instagram', 'facebook' => 'URL de Facebook',
];
add_action('admin_menu', function () {
  add_options_page('Armodent', 'Armodent', 'manage_options', 'armodent', function () {
    echo '<div class="wrap"><h1>Armodent</h1><form method="post" action="options.php">';
    settings_fields('armodent'); do_settings_sections('armodent'); submit_button();
    echo '</form></div>';
  });
});
add_action('admin_init', function () {
  register_setting('armodent', 'armodent_ajustes', ['sanitize_callback' => function ($in) {
    $out = [];
    foreach (ARMODENT_FIELDS as $k => $_) $out[$k] = sanitize_text_field($in[$k] ?? '');
    return $out;
  }]);
  add_settings_section('armodent_main', 'Datos de contacto', '__return_false', 'armodent');
  foreach (ARMODENT_FIELDS as $k => $label) {
    add_settings_field($k, $label, function () use ($k) {
      $v = get_option('armodent_ajustes', [])[$k] ?? '';
      printf('<input type="text" class="regular-text" name="armodent_ajustes[%s]" value="%s">', esc_attr($k), esc_attr($v));
    }, 'armodent', 'armodent_main');
  }
});

/* ---------- REST: ajustes + formulario ---------- */
add_action('rest_api_init', function () {
  register_rest_route('armodent/v1', '/ajustes', [
    'methods' => 'GET', 'permission_callback' => '__return_true',
    'callback' => function () {
      $a = get_option('armodent_ajustes', []);
      unset($a['email']); // el correo receptor no se expone públicamente
      return $a;
    },
  ]);

  register_rest_route('armodent/v1', '/contacto', [
    'methods' => 'POST', 'permission_callback' => '__return_true',
    'callback' => function (WP_REST_Request $r) {
      $d = $r->get_json_params() ?: [];
      if (!empty($d['web'])) return ['ok' => true]; // honeypot
      $nombre = sanitize_text_field($d['nombre'] ?? ''); $tel = sanitize_text_field($d['telefono'] ?? '');
      $email = sanitize_email($d['email'] ?? ''); $motivo = sanitize_text_field($d['motivo'] ?? '');
      $msg = sanitize_textarea_field($d['mensaje'] ?? '');
      if (!$nombre || !$tel || !is_email($email) || empty($d['consentimiento'])) return new WP_Error('datos', 'Datos incompletos', ['status' => 400]);

      // Límite simple: 5 envíos por hora por IP
      $key = 'armodent_rl_' . md5($_SERVER['REMOTE_ADDR'] ?? '');
      $n = (int) get_transient($key);
      if ($n >= 5) return new WP_Error('limite', 'Demasiados intentos', ['status' => 429]);
      set_transient($key, $n + 1, HOUR_IN_SECONDS);

      $to = get_option('armodent_ajustes', [])['email'] ?? '';
      if (!is_email($to)) $to = 'contacto@armodent.cl';
      $body = "Nombre: $nombre\nTeléfono: $tel\nCorreo: $email\nMotivo: $motivo\n\nMensaje:\n$msg\n";
      $ok = wp_mail($to, "Nueva consulta web — $nombre", $body, ["Reply-To: $nombre <$email>"]);
      return $ok ? ['ok' => true] : new WP_Error('envio', 'No se pudo enviar', ['status' => 500]);
    },
  ]);
});

/* ---------- CORS para el sitio Astro (ajusta el dominio si hace falta) ---------- */
add_filter('rest_pre_serve_request', function ($served) {
  $origin = get_http_origin();
  $allowed = ['https://armodent.cl', 'https://www.armodent.cl', 'http://localhost:4321'];
  if ($origin && in_array($origin, $allowed, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
  }
  return $served;
});
