<?php
// Laporan bulanan dalam format CSV (admin).

function report_export($in, $id) {
  need('admin');
  $m = $_GET['m'] ?? '';
  if (!preg_match('/^\d{4}-\d{2}$/', $m)) fail('Bulan tidak valid');
  $rt = rates();
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="laporan-ecobank-' . $m . '.csv"');
  $o = fopen('php://output', 'w');
  fwrite($o, "\xEF\xBB\xBF");
  fputcsv($o, ['Tanggal', 'Nasabah', 'Petugas', 'Jenis', 'Berat (kg)', 'Poin'], ',', '"', '\\');
  $rows = q("SELECT p.done, p.done_at, u.name un, t.name tn FROM pickups p JOIN users u ON u.id=p.uid LEFT JOIN users t ON t.id=p.pid WHERE p.status='selesai' AND to_char(p.done_at,'YYYY-MM')=? ORDER BY p.done_at, p.id", [$m]);
  foreach ($rows as $r) {
    foreach (json_decode($r['done'], true) as $x) {
      fputcsv($o, [$r['done_at'], cs($r['un']), cs($r['tn']), $x['type'], $x['kg'], round($x['kg'] * ($rt[$x['type']] ?? 0))], ',', '"', '\\');
    }
  }
  exit;
}
