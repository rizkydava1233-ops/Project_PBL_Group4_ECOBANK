<?php
// Penukaran poin dengan reward.

function redeem_create($in, $id) {
  $u = need('nasabah');
  $db = db();
  $db->beginTransaction();
  q('SELECT id FROM users WHERE id=? FOR UPDATE', [$u['id']]);
  q('SELECT id FROM rewards WHERE id=? FOR UPDATE', [$id]);
  $r = q('SELECT *, stock0 - (SELECT COUNT(*) FROM redeems WHERE rid=rewards.id) AS stock FROM rewards WHERE id=?', [$id])->fetch();
  if (!$r || (int)$r['stock'] < 1) { $db->rollBack(); fail('Stok reward habis'); }
  if (points($u['id']) < (int)$r['cost']) { $db->rollBack(); fail('Poin tidak cukup'); }
  q('INSERT INTO redeems(uid,rid,name,ic,cost,code) VALUES(?,?,?,?,?,?)',
    [$u['id'], $r['id'], $r['name'], $r['ic'], $r['cost'], 'ECO-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6))]);
  $db->commit();
  out(['ok' => true]);
}

function redeem_finish($in, $id) {
  need('admin');
  q("UPDATE redeems SET status='selesai' WHERE id=?", [$id]);
  out(['ok' => true]);
}
