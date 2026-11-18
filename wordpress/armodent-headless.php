<?php
/**
 * Plugin Name: Armodent Headless
 * Description: Tipos de contenido (Servicios, Preguntas frecuentes), ajustes de contacto y endpoint del formulario para el sitio Astro de armodent.cl despliegue automático en GitHub al cambiar contenido y modo "En construcción".
 * Version: 1.3.0
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

// Al final de cada petición de un editor en el escritorio, AJAX o REST: si el contenido cambió, despliega.
// También revisa las cargas normales (GET) del escritorio, así un reordenamiento que no se detectó
// en su momento se publica en la siguiente página que se abra. La huella solo se guarda si GitHub
// aceptó el despliegue: si falla, se reintenta en la siguiente petición.
add_action('shutdown', function () {
  if (!is_user_logged_in() || !current_user_can('edit_posts')) return;
  if (!is_admin() && !wp_doing_ajax() && !(defined('REST_REQUEST') && REST_REQUEST)) return;
  $fp = armodent_fingerprint();
  if ($fp === get_option('armodent_fingerprint')) return;
  // Evita disparos repetidos si hay varias peticiones simultáneas
  if (get_transient('armodent_dispatch_lock')) return;
  set_transient('armodent_dispatch_lock', 1, 20);
  if (armodent_dispatch('cambio en WordPress')) update_option('armodent_fingerprint', $fp, false);
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


/* ---------- Modo "En construcción" ----------
 * Botón en la barra superior y en Ajustes > Armodent. Al activarlo, escribe un bloque en el .htaccess
 * de la raíz del dominio (carpeta padre de WordPress) que responde 503 + la página de construcción a
 * todo el sitio, excepto /admin (WordPress). Es instantáneo: no requiere volver a publicar.
 * 503 = "no disponible temporalmente": Google no desindexa el sitio por esto.
 */
const ARMODENT_MARCA = 'Armodent en construccion';

function armodent_raiz() { return trailingslashit(dirname(untrailingslashit(ABSPATH))); }
function armodent_construccion_activa() { return (bool) get_option('armodent_construccion', false); }

// Solo el dueño (Raul) controla el modo "En construcción", aunque el cliente también sea administrador.
// El primer administrador que entra al escritorio con este plugin queda registrado como dueño.
function armodent_es_dueno() {
  $duenos = (array) get_option('armodent_duenos', []);
  return is_user_logged_in() && in_array(get_current_user_id(), array_map('intval', $duenos), true);
}
add_action('admin_init', function () {
  if (get_option('armodent_duenos') === false && current_user_can('manage_options'))
    update_option('armodent_duenos', [get_current_user_id()], false);
}, 1);

// Los demás administradores no ven el enlace "Desactivar" del plugin (desactivarlo publicaría el sitio)
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
  if (!armodent_es_dueno()) unset($links['deactivate']);
  return $links;
});

function armodent_htaccess_bloque() {
  $wp = trim(wp_parse_url(site_url(), PHP_URL_PATH) ?: '/admin', '/');
  return "# BEGIN " . ARMODENT_MARCA . "\n"
    . "<IfModule mod_rewrite.c>\n"
    . "RewriteEngine On\n"
    . "RewriteCond %{REQUEST_URI} !^/" . preg_quote($wp, '/') . "(/|$)\n"
    . "RewriteCond %{REQUEST_URI} !^/construccion\\.html$\n"
    . "RewriteCond %{REQUEST_URI} !^/\\.well-known/\n"
    . "RewriteCond %{REQUEST_URI} !^/cgi-bin/\n"
    . "RewriteRule ^ - [R=503,L]\n"
    . "</IfModule>\n"
    . "ErrorDocument 503 /construccion.html\n"
    . "# END " . ARMODENT_MARCA . "\n";
}

// Quita el bloque (si existe) y, si $activar, lo pone AL INICIO del .htaccess para que tenga prioridad.
function armodent_htaccess($activar) {
  $raiz = armodent_raiz();
  $file = $raiz . '.htaccess';
  if ($activar) {
    // La página de construcción viaja dentro del plugin y se copia a la raíz
    $src = plugin_dir_path(__FILE__) . 'construccion.html';
    if (!file_exists($src) || !@copy($src, $raiz . 'construccion.html')) return 'No se pudo copiar construccion.html a la raíz del sitio';
  }
  $actual = file_exists($file) ? file_get_contents($file) : '';
  if ($actual === false) return 'No se pudo leer el .htaccess de la raíz';
  $limpio = preg_replace('~# BEGIN ' . preg_quote(ARMODENT_MARCA, '~') . '.*?# END ' . preg_quote(ARMODENT_MARCA, '~') . "\\R?~s", '', $actual);
  $nuevo = $activar ? armodent_htaccess_bloque() . "\n" . ltrim($limpio) : ltrim($limpio);
  if ($nuevo === $actual) return true;
  if ($actual !== '') @copy($file, $raiz . '.htaccess.armodent-bak');
  if (file_put_contents($file, $nuevo, LOCK_EX) === false) return 'No se pudo escribir el .htaccess de la raíz (permisos)';
  return true;
}

add_action('admin_post_armodent_construccion', function () {
  if (!armodent_es_dueno()) wp_die('Sin permiso');
  check_admin_referer('armodent_construccion');
  $activar = ($_GET['estado'] ?? '') === 'on';
  $r = armodent_htaccess($activar);
  if ($r === true) update_option('armodent_construccion', $activar, false);
  set_transient('armodent_construccion_msg', $r === true
    ? ['ok' => true, 'msg' => $activar ? 'El sitio quedó en modo "En construcción".' : 'El sitio está publicado y visible para todos.']
    : ['ok' => false, 'msg' => $r], 60);
  wp_safe_redirect(wp_get_referer() ?: admin_url('options-general.php?page=armodent')); exit;
});

function armodent_construccion_url($on) {
  return wp_nonce_url(admin_url('admin-post.php?action=armodent_construccion&estado=' . ($on ? 'on' : 'off')), 'armodent_construccion');
}

// Botón en la barra superior (visible en todo el escritorio)
add_action('admin_bar_menu', function ($bar) {
  if (!armodent_es_dueno()) return;
  $on = armodent_construccion_activa();
  $bar->add_node([
    'id' => 'armodent-construccion',
    'title' => $on ? '<span style="color:#f5b18f">● Sitio en construcción</span>' : '<span style="color:#6fcf97">● Sitio publicado</span>',
    'href' => admin_url('options-general.php?page=armodent#armodent-construccion'),
  ]);
  $bar->add_node([
    'parent' => 'armodent-construccion', 'id' => 'armodent-construccion-toggle',
    'title' => $on ? 'Publicar el sitio' : 'Poner en construcción',
    'href' => armodent_construccion_url(!$on),
    'meta' => ['onclick' => "return confirm('" . ($on ? '¿Publicar el sitio para todos?' : '¿Ocultar el sitio y mostrar la página En construcción?') . "')"],
  ]);
}, 100);

// Sección en Ajustes > Armodent
add_action('admin_init', function () {
  if (!armodent_es_dueno()) return;
  add_settings_section('armodent_construccion', 'Estado del sitio', function () {
    $on = armodent_construccion_activa();
    echo '<div id="armodent-construccion" style="padding:14px 16px;border-left:4px solid ' . ($on ? '#f3a382' : '#00a32a') . ';background:#fff;max-width:640px">';
    echo $on
      ? '<p style="margin-top:0"><strong>En construcción.</strong> Los visitantes de armodent.cl ven la página "Sitio en construcción". WordPress (/admin) sigue funcionando y los cambios se siguen publicando, solo que no se ven.</p>'
      : '<p style="margin-top:0"><strong>Publicado.</strong> El sitio está visible para todos.</p>';
    printf('<a class="button %s" href="%s" onclick="return confirm(\'%s\')">%s</a>',
      $on ? 'button-primary' : '', esc_url(armodent_construccion_url(!$on)),
      $on ? '¿Publicar el sitio para todos?' : '¿Ocultar el sitio y mostrar la página En construcción?',
      $on ? 'Publicar el sitio' : 'Poner en construcción');
    echo '</div>';
  }, 'armodent');
}, 5);

add_action('admin_notices', function () {
  if ($m = get_transient('armodent_construccion_msg')) {
    delete_transient('armodent_construccion_msg');
    printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $m['ok'] ? 'success' : 'error', esc_html($m['msg']));
  }
  if (armodent_construccion_activa() && armodent_es_dueno())
    echo '<div class="notice notice-warning"><p><strong>armodent.cl está en modo "En construcción".</strong> Los visitantes no ven el sitio.</p></div>';
});

// Si se desactiva el plugin, el sitio vuelve a quedar visible
register_deactivation_hook(__FILE__, function () {
  if (armodent_construccion_activa() && !armodent_es_dueno()) return; // solo el dueño puede publicar el sitio
  armodent_htaccess(false);
  delete_option('armodent_construccion');
});

/* ---------- Corrección única de nombres de servicios (v1.2.0) ----------
 * Deja en WordPress los mismos nombres y slugs que usa el sitio.
 */
add_action('admin_init', function () {
  if (get_option('armodent_nombres_v120')) return;
  $cambios = [
    'medicina-estetica' => ['Estética facial', 'estetica-facial'],
    'ttm-trastornos-temporomandibulares' => ['TTM y dolor orofacial', 'ttm-dolor-orofacial'],
  ];
  foreach ($cambios as $viejo => [$titulo, $slug]) {
    $p = get_page_by_path($viejo, OBJECT, 'servicio');
    if ($p) wp_update_post(['ID' => $p->ID, 'post_title' => $titulo, 'post_name' => $slug]);
  }
  update_option('armodent_nombres_v120', 1, false);
});
