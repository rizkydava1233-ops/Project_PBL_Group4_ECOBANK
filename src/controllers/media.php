<?php
// Menyajikan foto setoran / bukti penjemputan hanya kepada pihak terkait.

function media_image($in, $id) {
  $u = need();
  $f = $_GET['f'] ?? '';
  $pid = (int)($_GET['id'] ?? 0);
  if (!in_array($f, ['photo', 'proof'])) fail('Tidak valid');
  $p = q("SELECT uid,pid,status,$f AS d FROM pickups WHERE id=?", [$pid])->fetch();
  $allowed = $p && $p['d'] && (
    $u['role'] === 'admin'
    || (int)$p['uid'] === (int)$u['id']
    || (int)$p['pid'] === (int)$u['id']
    || ($u['role'] === 'petugas' && $p['status'] === 'menunggu' && $f === 'photo')
  );
  if (!$allowed) fail('Tidak ditemukan', 404);
  header('Content-Type: image/jpeg');
  header('Cache-Control: private, max-age=3600');
  echo base64_decode(substr($p['d'], strlen('data:image/jpeg;base64,')));
  exit;
}
