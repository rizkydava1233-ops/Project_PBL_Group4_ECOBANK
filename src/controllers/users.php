<?php
// Manajemen pengguna oleh admin.

function user_approve($in, $id) {
  need('admin');
  if (!empty($in['ok'])) q("UPDATE users SET status='aktif' WHERE id=? AND role='petugas'", [$id]);
  else q("DELETE FROM users WHERE id=? AND status='menunggu'", [$id]);
  out(['ok' => true]);
}

function user_delete($in, $id) {
  need('admin');
  q("DELETE FROM users WHERE id=? AND role<>'admin'", [$id]);
  out(['ok' => true]);
}

function user_reset($in, $id) {
  need('admin');
  q("UPDATE users SET pass_hash=? WHERE id=? AND role<>'admin'", [password_hash('123456', PASSWORD_DEFAULT), $id]);
  out(['ok' => true]);
}
