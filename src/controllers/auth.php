<?php
// Masuk, daftar, keluar.

function auth_login($in, $id) {
  $e = strtolower(trim($in['email'] ?? ''));
  $x = q('SELECT * FROM users WHERE email=?', [$e])->fetch();
  if (!$x || !password_verify($in['pass'] ?? '', $x['pass_hash'])) { usleep(400000); fail('Email atau kata sandi salah', 401); }
  if ($x['status'] !== 'aktif') fail('Akun petugas menunggu persetujuan admin', 403);
  session_regenerate_id(true);
  $_SESSION['uid'] = (int)$x['id'];
  out(['ok' => true]);
}

function auth_register($in, $id) {
  $name = trim($in['name'] ?? '');
  $e = strtolower(trim($in['email'] ?? ''));
  $p = (string)($in['pass'] ?? '');
  $role = $in['role'] ?? '';
  if ($name === '' || mb_strlen($name) > 80 || !filter_var($e, FILTER_VALIDATE_EMAIL) || strlen($p) < 6 || !in_array($role, ['nasabah', 'petugas'])) fail('Data pendaftaran tidak lengkap atau tidak valid');
  if (q('SELECT 1 FROM users WHERE email=?', [$e])->fetch()) fail('Email sudah terdaftar');
  $st = $role === 'petugas' ? 'menunggu' : 'aktif';
  $nid = q('INSERT INTO users(name,email,pass_hash,role,status,addr) VALUES(?,?,?,?,?,?) RETURNING id',
    [$name, $e, password_hash($p, PASSWORD_DEFAULT), $role, $st, mb_substr(trim($in['addr'] ?? ''), 0, 300)])->fetchColumn();
  if ($st === 'aktif') { session_regenerate_id(true); $_SESSION['uid'] = (int)$nid; }
  out(['ok' => true, 'pending' => $st === 'menunggu']);
}

function auth_logout($in, $id) {
  session_destroy();
  out(['ok' => true]);
}
