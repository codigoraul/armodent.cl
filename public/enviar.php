<?php
// Envío del formulario de contacto de armodent.cl (cPanel, sin WordPress).
date_default_timezone_set('America/Santiago');
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

const DESTINO = 'contacto@armodent.cl, codigoraul@gmail.com'; // varios destinatarios separados por coma
const REMITENTE = 'contacto@armodent.cl'; // debe ser una cuenta del dominio para no caer en spam

function salir(int $code, array $data) { http_response_code($code); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') salir(405, ['ok' => false, 'error' => 'Método no permitido']);

// Solo desde el propio sitio
$origen = $_SERVER['HTTP_ORIGIN'] ?? '';
$permitidos = ['https://armodent.cl', 'https://www.armodent.cl', 'http://localhost:4321'];
if ($origen !== '' && !in_array($origen, $permitidos, true)) salir(403, ['ok' => false, 'error' => 'Origen no permitido']);

$d = json_decode(file_get_contents('php://input'), true);
if (!is_array($d)) $d = $_POST;

if (!empty($d['web'])) salir(200, ['ok' => true]); // honeypot: bots

$limpia = fn($v) => trim(preg_replace('/[\r\n]+/', ' ', strip_tags((string)($v ?? ''))));
$nombre = $limpia($d['nombre'] ?? '');
$tel    = $limpia($d['telefono'] ?? '');
$email  = trim((string)($d['email'] ?? ''));
$motivo = $limpia($d['motivo'] ?? '');
$msg    = trim(strip_tags((string)($d['mensaje'] ?? '')));

if ($nombre === '' || $tel === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($d['consentimiento'])) {
  salir(400, ['ok' => false, 'error' => 'Datos incompletos']);
}
if (mb_strlen($nombre) > 120 || mb_strlen($msg) > 3000) salir(400, ['ok' => false, 'error' => 'Texto demasiado largo']);

// Límite simple: 5 envíos por hora por IP
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$arch = sys_get_temp_dir() . '/armodent_rl_' . md5($ip);
$intentos = [];
if (is_file($arch)) $intentos = array_filter(array_map('intval', explode(',', (string)file_get_contents($arch))), fn($t) => $t > time() - 3600);
if (count($intentos) >= 5) salir(429, ['ok' => false, 'error' => 'Demasiados intentos']);
$intentos[] = time();
@file_put_contents($arch, implode(',', $intentos));

$asunto = 'Nueva consulta web — ' . $nombre;
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$fecha = date('d-m-Y H:i');

// Versión texto (respaldo)
$texto = "Nueva consulta web\n\nNombre: $nombre\nTeléfono: $tel\nCorreo: $email\nMotivo: $motivo\n\nMensaje:\n$msg\n\nRecibida el $fecha desde armodent.cl\n";

// Versión HTML con estilos en línea (los correos solo aceptan CSS inline)
$fila = fn($etq, $val) => '<tr><td style="padding:12px 0;border-bottom:1px solid #fedecc;width:130px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#4d525d;vertical-align:top">' . $etq . '</td><td style="padding:12px 0;border-bottom:1px solid #fedecc;font-size:16px;color:#111827">' . $val . '</td></tr>';
$wa = preg_replace('/\D/', '', $tel);
$html = '<!doctype html><html lang="es"><body style="margin:0;padding:0;background:#fff8f5">'
  . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#fff8f5;padding:32px 12px"><tr><td align="center">'
  . '<table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;font-family:Helvetica,Arial,sans-serif">'
  . '<tr><td style="background:#111827;padding:26px 32px"><div style="font-family:Georgia,serif;font-size:24px;letter-spacing:.14em;color:#fff7f4">ARMODENT</div><div style="margin-top:4px;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#fab190">Odontología y estética facial</div></td></tr>'
  . '<tr><td style="padding:32px 32px 8px"><div style="font-family:Georgia,serif;font-size:26px;color:#111827">Nueva consulta web</div><div style="margin-top:6px;font-size:14px;color:#4d525d">Recibida el ' . $fecha . '</div></td></tr>'
  . '<tr><td style="padding:8px 32px 8px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0">'
  . $fila('Nombre', $h($nombre))
  . $fila('Teléfono', '<a href="tel:+' . $h($wa) . '" style="color:#111827;text-decoration:none">' . $h($tel) . '</a>')
  . $fila('Correo', '<a href="mailto:' . $h($email) . '" style="color:#111827">' . $h($email) . '</a>')
  . $fila('Motivo', $h($motivo))
  . '</table></td></tr>'
  . ($msg !== '' ? '<tr><td style="padding:16px 32px 8px"><div style="font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#4d525d">Mensaje</div><div style="margin-top:8px;padding:16px 18px;background:#fff8f5;border-left:3px solid #fab190;font-size:16px;line-height:1.6;color:#111827">' . nl2br($h($msg)) . '</div></td></tr>' : '')
  . '<tr><td style="padding:24px 32px 32px"><a href="mailto:' . $h($email) . '?subject=' . rawurlencode('Tu consulta en ARMODENT') . '" style="display:inline-block;background:#fab190;color:#111827;font-size:15px;font-weight:600;text-decoration:none;padding:14px 26px;border-radius:4px">Responder al paciente</a>'
  . ' <a href="https://wa.me/' . $h($wa) . '" style="display:inline-block;margin-left:8px;color:#111827;font-size:15px;text-decoration:underline">Escribir por WhatsApp</a></td></tr>'
  . '<tr><td style="background:#fff7f4;padding:16px 32px;font-size:12px;color:#4d525d">Enviado desde el formulario de armodent.cl</td></tr>'
  . '</table></td></tr></table></body></html>';

$lim = 'b_' . md5(uniqid('', true));
$cabeceras = [
  'From: ARMODENT Web <' . REMITENTE . '>',
  'Reply-To: ' . $nombre . ' <' . $email . '>',
  'MIME-Version: 1.0',
  'Content-Type: multipart/alternative; boundary="' . $lim . '"',
  'X-Mailer: PHP/' . phpversion(),
];
$cuerpo = "--$lim\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($texto))
  . "--$lim\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($html))
  . "--$lim--";
$ok = mail(DESTINO, '=?UTF-8?B?' . base64_encode($asunto) . '?=', $cuerpo, implode("\r\n", $cabeceras), '-f' . REMITENTE);

salir($ok ? 200 : 500, $ok ? ['ok' => true] : ['ok' => false, 'error' => 'No se pudo enviar']);
