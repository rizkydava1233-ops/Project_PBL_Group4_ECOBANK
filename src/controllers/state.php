<?php
// Seluruh data yang dibutuhkan front-end, disaring menurut peran.

function state_show($in, $id) {
  $u = me();
  if (!$u) out(['me' => null]);
  $r = $u['role'];

  $users = [];
  foreach (q("SELECT id,name,email,role,status,addr,bonus, bonus + COALESCE((SELECT SUM(pts) FROM pickups WHERE uid=users.id AND status='selesai'),0) - COALESCE((SELECT SUM(cost) FROM redeems WHERE uid=users.id),0) AS points FROM users ORDER BY id") as $x) {
    $x = ['id' => (int)$x['id'], 'name' => $x['name'], 'email' => $x['email'], 'role' => $x['role'], 'status' => $x['status'], 'addr' => $x['addr'], 'bonus' => (int)$x['bonus'], 'points' => (int)$x['points']];
    if ($r === 'admin' || $x['id'] === (int)$u['id']) $users[] = $x;
    else $users[] = ['id' => $x['id'], 'name' => $x['name'], 'role' => $x['role'], 'status' => $x['status']];
  }

  $cols = 'id,uid,pid,items,done,addr,when_date,slot,status,pts,actual,done_at,created_at,(photo IS NOT NULL) AS has_photo,(proof IS NOT NULL) AS has_proof';
  if ($r === 'admin')        $pk = q("SELECT $cols FROM pickups ORDER BY id DESC");
  elseif ($r === 'nasabah')  $pk = q("SELECT $cols FROM pickups WHERE uid=? ORDER BY id DESC", [$u['id']]);
  else                       $pk = q("SELECT $cols FROM pickups WHERE status='menunggu' OR pid=? ORDER BY id DESC", [$u['id']]);

  if ($r === 'admin')        $rd = q('SELECT * FROM redeems ORDER BY id DESC');
  elseif ($r === 'nasabah')  $rd = q('SELECT * FROM redeems WHERE uid=? ORDER BY id DESC', [$u['id']]);
  else                       $rd = null;

  out(['me' => ['id' => (int)$u['id']], 'state' => [
    'users' => $users,
    'rates' => rates(),
    'pickups' => array_map('pk', $pk->fetchAll()),
    'redeems' => $rd ? array_map(fn($x) => [
      'id' => (int)$x['id'], 'uid' => (int)$x['uid'], 'rid' => $x['rid'] ? (int)$x['rid'] : null,
      'name' => $x['name'], 'ic' => $x['ic'], 'cost' => (int)$x['cost'], 'status' => $x['status'], 'code' => $x['code'],
      'date' => date('j/n/Y', strtotime($x['created_at'])),
    ], $rd->fetchAll()) : [],
    'rewards' => array_map(fn($x) => [
      'id' => (int)$x['id'], 'ic' => $x['ic'], 'name' => $x['name'], 'cost' => (int)$x['cost'],
      'stock0' => (int)$x['stock0'], 'stock' => (int)$x['stock'],
    ], q('SELECT *, stock0 - (SELECT COUNT(*) FROM redeems WHERE rid=rewards.id) AS stock FROM rewards ORDER BY id')->fetchAll()),
    'posts' => array_map(fn($x) => [
      'id' => (int)$x['id'], 'title' => $x['title'], 'body' => $x['body'], 'kind' => $x['kind'],
      'date' => date('j/n/Y', strtotime($x['created_at'])),
    ], q('SELECT * FROM posts ORDER BY id DESC LIMIT 30')->fetchAll()),
    'board' => array_map(fn($x) => ['id' => (int)$x['id'], 'name' => $x['name'], 'kg' => (float)$x['kg']],
      q("SELECT u.id,u.name,COALESCE(SUM(p.actual),0) AS kg FROM users u LEFT JOIN pickups p ON p.uid=u.id AND p.status='selesai' WHERE u.role='nasabah' AND u.status='aktif' GROUP BY u.id ORDER BY kg DESC, u.id LIMIT 5")->fetchAll()),
  ]]);
}
