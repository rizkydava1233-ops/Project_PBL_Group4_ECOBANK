<?php
// Permintaan penjemputan: nasabah membuat/membatalkan, petugas mengambil/menyelesaikan.

function pickup_create($in, $id) {
  $u = need('nasabah');
  $rt = rates();
  $items = [];
  foreach ((array)($in['items'] ?? []) as $i) {
    $t = $i['type'] ?? '';
    $kg = (float)($i['kg'] ?? 0);
    if (!isset($rt[$t]) || $kg <= 0 || $kg > 1000) fail('Data jenis atau berat sampah tidak valid');
    $items[] = ['type' => $t, 'kg' => round($kg, 2)];
  }
  if (!$items || count($items) > 10) fail('Isi minimal satu jenis sampah');
  $addr = trim($in['addr'] ?? '');
  $when = $in['when'] ?? '';
  $slot = $in['slot'] ?? '';
  if ($addr === '' || mb_strlen($addr) > 300) fail('Alamat wajib diisi');
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $when) || $when < date('Y-m-d')) fail('Tanggal penjemputan tidak valid');
  if (!in_array($slot, SLOTS, true)) fail('Jam penjemputan tidak valid');
  q('INSERT INTO pickups(uid,items,addr,when_date,slot,photo) VALUES(?,?,?,?,?,?)',
    [$u['id'], json_encode($items), $addr, $when, $slot, img($in['photo'] ?? null)]);
  out(['ok' => true]);
}

function pickup_cancel($in, $id) {
  $u = need('nasabah');
  $n = q("UPDATE pickups SET status='dibatalkan' WHERE id=? AND uid=? AND status IN ('menunggu','diterima')", [$id, $u['id']])->rowCount();
  $n ? out(['ok' => true]) : fail('Permintaan tidak bisa dibatalkan');
}

function pickup_take($in, $id) {
  $u = need('petugas');
  $n = q("UPDATE pickups SET status='diterima', pid=? WHERE id=? AND status='menunggu'", [$u['id'], $id])->rowCount();
  $n ? out(['ok' => true]) : fail('Tugas sudah diambil petugas lain atau dibatalkan');
}

function pickup_go($in, $id) {
  $u = need('petugas');
  $n = q("UPDATE pickups SET status='berangkat' WHERE id=? AND pid=? AND status='diterima'", [$id, $u['id']])->rowCount();
  $n ? out(['ok' => true]) : fail('Status tidak dapat diubah');
}

function pickup_done($in, $id) {
  $u = need('petugas');
  $p = q("SELECT * FROM pickups WHERE id=? AND pid=? AND status='berangkat'", [$id, $u['id']])->fetch();
  if (!$p) fail('Tugas tidak ditemukan');
  $items = json_decode($p['items'], true);
  $kg = array_values((array)($in['kg'] ?? []));
  if (count($kg) !== count($items)) fail('Berat aktual belum lengkap');
  $rt = rates();
  $done = [];
  $pts = 0;
  $tot = 0;
  foreach ($items as $i => $it) {
    $k = round((float)$kg[$i], 2);
    if ($k < 0 || $k > 1000) fail('Berat tidak valid');
    $done[] = ['type' => $it['type'], 'kg' => $k];
    $pts += (int)round($k * ($rt[$it['type']] ?? 0));
    $tot += $k;
  }
  if ($tot <= 0) fail('Berat total harus lebih dari 0');
  q("UPDATE pickups SET status='selesai', done=?, actual=?, pts=?, done_at=CURRENT_DATE, proof=? WHERE id=?",
    [json_encode($done), $tot, $pts, img($in['proof'] ?? null), $id]);
  out(['ok' => true, 'pts' => $pts]);
}
