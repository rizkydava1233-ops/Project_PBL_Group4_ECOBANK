<?php
// Inisialisasi: pengaturan PHP, konfigurasi, sesi, polyfill, dan penangan error.
ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start(); // jangan pernah mencetak error PHP (HTML) ke respons JSON

require dirname(__DIR__) . '/config.php';
require __DIR__ . '/response.php';
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Folder sesi Laragon kadang tidak ada
$sp = session_save_path();
if (!$sp || !is_dir($sp) || !is_writable($sp)) session_save_path(sys_get_temp_dir());
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'path' => '/']);
session_start();

if (!function_exists('mb_strlen')) {
  function mb_strlen($s) { return preg_match_all('/./us', (string)$s); }
}
if (!function_exists('mb_substr')) {
  function mb_substr($s, $a, $l = null) { return implode('', array_slice(preg_split('//u', (string)$s, -1, PREG_SPLIT_NO_EMPTY), $a, $l)); }
}

set_error_handler(function ($n, $m, $f, $l) {
  if (error_reporting() & $n) error_log("PHP: $m ($f:$l)");
  return true;
});
set_exception_handler(function ($e) {
  error_log($e);
  fail('Terjadi kesalahan pada server' . dbg($e), 500);
});
