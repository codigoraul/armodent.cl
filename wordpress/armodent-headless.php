<?php
/**
 * Plugin Name: Armodent Headless
 * Description: Tipos de contenido (Servicios, Preguntas frecuentes), ajustes de contacto y endpoint del formulario para el sitio Astro de armodent.cl y despliegue automático en GitHub al cambiar contenido.
 * Version: 1.1.0
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

/* ---------- Despliegue automático (GitHub Actions) ----------
 * Ajustes > Armodent > "Publicación automática": URL de despliegue + token (como en Reserman).
 * Detecta cualquier cambio en Servicios, Preguntas frecuentes o Ajustes > Armodent
 * (crear, editar, borrar, reordenar, incluso desde plugins de orden) y lanza el workflow.
 */
function armodent_gh() {
  $o = get_option('armodent_deploy', []);
  $url = trim($o['url'] ?? ''); $repo = '';
  // Acepta la URL de la API (…/repos/usuario/repo/dispatches), el link del repo o "usuario/repo"
  if (preg_match('~api\.github\.com/repos/([^/\s]+/[^/\s]+)/dispatches~i', $url, $m)) $repo = $m[1];
  elseif (preg_match('~github\.com[/:]([^/\s]+/[^/\s]+?)(?:\.git)?/?$~i', $url, $m)) $repo = $m[1];
  elseif (preg_match('~^[\w.-]+/[\w.-]+$~', $url)) $repo = $url;
  if ($repo) $url = 'https://api.github.com/repos/' . $repo . '/dispatches';
  return ['url' => $url, 'repo' => $repo, 'token' => trim($o['token'] ?? '')];
}
function armodent_gh_repo() { return armodent_gh()['repo']; }

function armodent_fingerprint() {
  global $wpdb;
  $rows = $wpdb->get_results("SELECT ID, post_status, menu_order, post_modified_gmt FROM {$wpdb->posts}
    WHERE post_type IN ('servicio','faq') AND post_status IN ('publish','draft','pending','private','future','trash')
    ORDER BY ID", ARRAY_N);
  return md5(wp_json_encode([$rows, get_option('armodent_ajustes', [])]));
}

function armodent_dispatch($motivo = 'contenido') {
  $gh = armodent_gh();
  if (!$gh['url']) {
    update_option('armodent_deploy_estado', ['t' => time(), 'ok' => false, 'msg' => 'Falta la URL de despliegue en Ajustes > Armodent'], false);
    return false;
  }
  // Con token: GitHub repository_dispatch (evento wp-update). Sin token: POST simple a la URL.
  $headers = ['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28', 'User-Agent' => 'armodent-wp', 'Content-Type' => 'application/json'];
  if ($gh['token']) $headers['Authorization'] = 'Bearer ' . $gh['token'];
  $res = wp_remote_post($gh['url'], [
    'timeout' => 8,
    'headers' => $headers,
    'body' => wp_json_encode(['event_type' => 'wp-update', 'client_payload' => ['motivo' => $motivo]]),
  ]);
  $code = is_wp_error($res) ? 0 : wp_remote_retrieve_response_code($res);
  $ok = $code >= 200 && $code < 300;
  update_option('armodent_deploy_estado', ['t' => time(), 'ok' => $ok,
    'msg' => $ok ? "Despliegue solicitado ($motivo)" : ('Error ' . ($code ?: $res->get_error_message()) . ' al contactar GitHub')], false);
  return $ok;
}

// Al final de cada petición de un editor: si el contenido cambió, despliega.
add_action('shutdown', function () {
  if (!is_user_logged_in() || !current_user_can('edit_posts')) return;
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && !wp_doing_ajax()) return; // las ediciones son POST/AJAX/REST
  $fp = armodent_fingerprint();
  if ($fp === get_option('armodent_fingerprint')) return;
  update_option('armodent_fingerprint', $fp, false);
  armodent_dispatch('cambio en WordPress');
});

// Primera huella al entrar al escritorio, para que el primer cambio ya despliegue
add_action('admin_init', function () {
  if (get_option('armodent_fingerprint') === false) update_option('armodent_fingerprint', armodent_fingerprint(), false);
});

// Publicación automática (solo administrador) en Ajustes > Armodent
add_action('admin_init', function () {
  register_setting('armodent', 'armodent_deploy', ['sanitize_callback' => function ($in) {
    $prev = get_option('armodent_deploy', []);
    if (!current_user_can('manage_options')) return $prev;
    return ['url' => sanitize_text_field(trim($in['url'] ?? '')), 'token' => sanitize_text_field(trim($in['token'] ?? ''))];
  }]);
  if (!current_user_can('manage_options')) return;
  add_settings_section('armodent_deploy', 'Publicación automática (solo administrador)', function () {
    echo '<p>El sitio es estático: al guardar cambios se debe reconstruir. Si se configura una URL, el plugin la llama cada vez que se crea, edita, borra o reordena un servicio o una pregunta frecuente, o se guardan los datos de contacto.</p>';
  }, 'armodent');
  add_settings_field('deploy_url', 'URL de despliegue', function () {
    printf('<input type="text" class="regular-text" name="armodent_deploy[url]" value="%s" placeholder="https://api.github.com/repos/USUARIO/REPO/dispatches">',
      esc_attr(get_option('armodent_deploy', [])['url'] ?? ''));
  }, 'armodent', 'armodent_deploy');
  add_settings_field('deploy_token', 'Token (opcional)', function () {
    printf('<input type="password" class="regular-text" name="armodent_deploy[token]" value="%s" autocomplete="off">', esc_attr(get_option('armodent_deploy', [])['token'] ?? ''));
    echo '<p class="description">Con token se envía como GitHub <code>repository_dispatch</code> (evento <code>wp-update</code>); sin token, un POST simple.</p>';
  }, 'armodent', 'armodent_deploy');
  add_settings_field('deploy_estado', 'Estado', function () {
    $e = get_option('armodent_deploy_estado');
    if ($e) printf('<p>Último intento: %s — <span style="color:%s">%s</span></p>',
      esc_html(wp_date('d-m-Y H:i', $e['t'])), $e['ok'] ? '#0a7d33' : '#b32d2e', esc_html($e['msg']));
    printf('<p><a class="button" href="%s">Publicar ahora</a>', esc_url(wp_nonce_url(admin_url('admin-post.php?action=armodent_deploy'), 'armodent_deploy')));
    if ($r = armodent_gh()['repo']) printf(' <a href="https://github.com/%s/actions" target="_blank" rel="noopener">Ver en GitHub</a>', esc_attr($r));
    echo '</p>';
  }, 'armodent', 'armodent_deploy');
});

add_action('admin_post_armodent_deploy', function () {
  if (!current_user_can('edit_posts')) wp_die('Sin permiso');
  check_admin_referer('armodent_deploy');
  armodent_dispatch('manual');
  wp_safe_redirect(admin_url('options-general.php?page=armodent')); exit;
});
