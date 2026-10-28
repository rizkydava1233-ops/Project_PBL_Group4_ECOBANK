<?php
// Reward dan tarif poin (admin).

function reward_save($in, $id) {
  need('admin');
  $name = trim($in['name'] ?? '');
  $cost = (int)($in['cost'] ?? 0);
  $stock = (int)($in['stock'] ?? -1);
  $ic = $in['ic'] ?? 'gift';
  if ($name === '' || $cost <= 0 || $stock < 0 || !in_array($ic, ['gift', 'bag', 'sprout', 'plant', 'soap', 'cup', 'ticket'])) fail('Data reward tidak valid');
  q('INSERT INTO rewards(ic,name,cost,stock0) VALUES(?,?,?,?)', [$ic, mb_substr($name, 0, 80), $cost, $stock]);
  out(['ok' => true]);
}

function reward_stock($in, $id) {
  need('admin');
  $d = (int)($in['d'] ?? 0);
  $n = q('UPDATE rewards SET stock0 = stock0 + ? WHERE id=? AND stock0 + ? >= (SELECT COUNT(*) FROM redeems WHERE rid=rewards.id)', [$d, $id, $d])->rowCount();
  $n ? out(['ok' => true]) : fail('Stok tidak boleh kurang dari 0');
}

function reward_delete($in, $id) {
  need('admin');
  q('DELETE FROM rewards WHERE id=?', [$id]);
  out(['ok' => true]);
}

function rates_save($in, $id) {
  need('admin');
  foreach ((array)($in['rates'] ?? []) as $k => $v) {
    if ((int)$v > 0) q('UPDATE rates SET points=? WHERE type=?', [(int)$v, $k]);
  }
  out(['ok' => true]);
}
