<?php
// Titik masuk API EcoBank (front controller). Logika ada di folder src/.
require __DIR__ . '/src/bootstrap.php';
$routes = require __DIR__ . '/src/routes.php';

$a = $_GET['a'] ?? '';
$post = $_SERVER['REQUEST_METHOD'] === 'POST';
if ($post && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'eco') fail('Permintaan tidak valid', 403);
$in = $post ? (json_decode(file_get_contents('php://input'), true) ?: []) : [];
$id = (int)($in['id'] ?? 0);

if (!isset($routes[$a])) fail('Aksi tidak dikenal', 404);

try {
  $routes[$a]($in, $id);
} catch (Throwable $e) {
  if (db()->inTransaction()) db()->rollBack();
  error_log($e);
  fail('Terjadi kesalahan pada server' . dbg($e), 500);
}
