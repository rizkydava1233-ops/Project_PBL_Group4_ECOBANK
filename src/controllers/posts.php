<?php
// Konten edukasi dan informasi (admin).

function post_save($in, $id) {
  need('admin');
  $t = trim($in['title'] ?? '');
  $b = trim($in['body'] ?? '');
  $k = $in['kind'] ?? '';
  if ($t === '' || $b === '' || !in_array($k, ['edu', 'info'])) fail('Judul, isi, dan jenis konten wajib diisi');
  q('INSERT INTO posts(title,body,kind) VALUES(?,?,?)', [mb_substr($t, 0, 120), mb_substr($b, 0, 600), $k]);
  out(['ok' => true]);
}

function post_delete($in, $id) {
  need('admin');
  q('DELETE FROM posts WHERE id=?', [$id]);
  out(['ok' => true]);
}
