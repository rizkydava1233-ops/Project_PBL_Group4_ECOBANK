<?php
// Pengguna yang sedang login dan pengecekan hak akses per peran.

function me() {
  static $u = false;
  if ($u === false) {
    $u = isset($_SESSION['uid']) ? q("SELECT * FROM users WHERE id=? AND status='aktif'", [$_SESSION['uid']])->fetch() : null;
  }
  return $u ?: null;
}

function need(...$roles) {
  $u = me();
  if (!$u) fail('Silakan masuk terlebih dahulu', 401);
  if ($roles && !in_array($u['role'], $roles)) fail('Anda tidak punya akses', 403);
  return $u;
}
