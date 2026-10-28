<?php
// Fungsi keluaran JSON.

function out($d, $c = 200) {
  while (ob_get_level()) ob_end_clean();
  http_response_code($c);
  echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

function fail($m, $c = 400) { out(['error' => $m], $c); }

// Detail error hanya ditampilkan bila DEBUG aktif (atau tidak didefinisikan)
function dbg($e) { return (!defined('DEBUG') || DEBUG) ? ': ' . $e->getMessage() : ''; }
