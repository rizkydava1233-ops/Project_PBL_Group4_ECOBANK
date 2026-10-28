<?php
// Konstanta dan fungsi bantu yang dipakai lintas controller.

const SLOTS = ['Pagi (08.00-11.00)', 'Siang (11.00-14.00)', 'Sore (14.00-17.00)'];

function rates() {
  $r = [];
  foreach (q('SELECT * FROM rates ORDER BY points DESC') as $x) $r[$x['type']] = (int)$x['points'];
  return $r;
}

// Saldo poin dihitung di database: bonus + setoran selesai - penukaran
function points($uid) {
  return (int)q("SELECT u.bonus + COALESCE((SELECT SUM(pts) FROM pickups WHERE uid=u.id AND status='selesai'),0) - COALESCE((SELECT SUM(cost) FROM redeems WHERE uid=u.id),0) FROM users u WHERE u.id=?", [$uid])->fetchColumn();
}

// Validasi foto data-URI JPEG
function img($d) {
  if (!$d) return null;
  if (!is_string($d) || strlen($d) > 450000 || !preg_match('#^data:image/jpeg;base64,[A-Za-z0-9+/=]+$#', $d)) fail('Foto tidak valid (gunakan JPEG, maks. sekitar 300 KB)');
  return $d;
}

// Baris pickups -> bentuk JSON untuk front-end
function pk($r) {
  $it = json_decode($r['items'], true);
  $id = (int)$r['id'];
  return [
    'id' => $id, 'uid' => (int)$r['uid'], 'pid' => $r['pid'] ? (int)$r['pid'] : null,
    'items' => $it, 'type' => $it[0]['type'], 'kg' => array_sum(array_column($it, 'kg')),
    'done' => $r['done'] ? json_decode($r['done'], true) : null,
    'actual' => $r['actual'] !== null ? (float)$r['actual'] : null,
    'pts' => (int)$r['pts'], 'doneAt' => $r['done_at'],
    'when' => $r['when_date'], 'slot' => $r['slot'], 'addr' => $r['addr'], 'status' => $r['status'],
    'date' => date('j/n/Y', strtotime($r['created_at'])),
    'photo' => $r['has_photo'] ? "api.php?a=img&id=$id&f=photo" : null,
    'proof' => $r['has_proof'] ? "api.php?a=img&id=$id&f=proof" : null,
  ];
}

// Cegah CSV injection
function cs($v) {
  $v = (string)$v;
  return $v !== '' && strpos('=+-@', $v[0]) !== false ? "'" . $v : $v;
}
