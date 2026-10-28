<?php
// Jalankan sekali: http://localhost/ecobank/setup.php  (membuat database, tabel, dan data awal)
require __DIR__ . '/config.php';
header('Content-Type: text/html; charset=utf-8');
$log = [];
try {
  if (file_exists(__DIR__ . '/setup.lock')) throw new Exception('Setup sudah pernah dijalankan. Hapus file setup.lock jika ingin mengulang.');
  $opt = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
  $root = new PDO(db_dsn('postgres'), DB_USER, DB_PASS, $opt);
  if (!$root->query("SELECT 1 FROM pg_database WHERE datname='" . DB_NAME . "'")->fetch()) {
    $root->exec('CREATE DATABASE "' . DB_NAME . '"'); $log[] = 'Database ' . DB_NAME . ' dibuat.';
  }
  $db = new PDO(db_dsn(), DB_USER, DB_PASS, $opt);
  // Cegah bentrok dengan tabel milik aplikasi lain di database yang sama
  $marker = ['users' => 'pass_hash', 'rates' => 'points', 'rewards' => 'stock0', 'pickups' => 'when_date', 'redeems' => 'code', 'posts' => 'kind'];
  $bentrok = [];
  foreach ($marker as $t => $col) {
    $cols = $db->query("SELECT column_name FROM information_schema.columns WHERE table_schema='public' AND table_name='$t'")->fetchAll(PDO::FETCH_COLUMN);
    if ($cols && !in_array($col, $cols)) $bentrok[] = $t;
  }
  if ($bentrok) throw new Exception('Database "' . DB_NAME . '" sudah berisi tabel milik aplikasi lain (' . implode(', ', $bentrok) . '). Ubah DB_NAME di config.php menjadi nama baru, misalnya ecobank_php, lalu buka setup.php lagi. Data lama tidak disentuh.');
  $db->exec(file_get_contents(__DIR__ . '/database/schema.sql')); $log[] = 'Tabel siap.';
  if (!$db->query('SELECT 1 FROM users LIMIT 1')->fetch()) {
    $u = $db->prepare('INSERT INTO users(name,email,pass_hash,role,status,addr,bonus) VALUES(?,?,?,?,?,?,?)');
    $u->execute(['Admin Eco', 'admin@eco.id', password_hash('admin123', PASSWORD_DEFAULT), 'admin', 'aktif', null, 0]);
    $u->execute(['Budi Santoso', 'nasabah@eco.id', password_hash('123456', PASSWORD_DEFAULT), 'nasabah', 'aktif', 'Jl. Veteran 12, Malang', 650]);
    $u->execute(['Rani Petugas', 'petugas@eco.id', password_hash('123456', PASSWORD_DEFAULT), 'petugas', 'aktif', null, 0]);
    foreach (['Plastik' => 120, 'Kertas' => 80, 'Logam' => 200, 'Kaca' => 60, 'Elektronik' => 300, 'Organik' => 40] as $k => $v)
      $db->prepare('INSERT INTO rates VALUES(?,?)')->execute([$k, $v]);
    foreach ([['bag', 'Tote Bag Daur Ulang', 500, 20], ['sprout', 'Pupuk Kompos 1 kg', 300, 30], ['soap', 'Sabun Organik', 400, 15], ['cup', 'Tumbler Bambu', 900, 8], ['plant', 'Bibit Tanaman', 250, 25], ['ticket', 'Voucher Belanja Rp25.000', 1500, 10]] as $r)
      $db->prepare('INSERT INTO rewards(ic,name,cost,stock0) VALUES(?,?,?,?)')->execute($r);
    foreach ([['Pisahkan sampah basah dan kering', 'Sampah organik dipisah dari anorganik agar bernilai lebih dan mudah didaur ulang.', 'edu'], ['Bersihkan sebelum disetor', 'Bilas botol dan wadah plastik lalu keringkan agar diterima dengan nilai poin penuh.', 'edu'], ['Selamat datang di EcoBank', 'Setor sampahmu, kumpulkan poin, lalu tukarkan dengan produk ramah lingkungan.', 'info']] as $p)
      $db->prepare('INSERT INTO posts(title,body,kind) VALUES(?,?,?)')->execute($p);
    $log[] = 'Data awal dimasukkan (akun demo, tarif, reward, konten).';
  }
  file_put_contents(__DIR__ . '/setup.lock', date('c')); $log[] = 'Selesai. Buka <a href="index.html">aplikasi</a>.';
  $ok = true;
} catch (Throwable $e) { $ok = false; $log[] = 'Gagal: ' . htmlspecialchars($e->getMessage()); }
echo '<body style="font-family:system-ui;max-width:560px;margin:40px auto;line-height:1.7"><h2>' . ($ok ? 'Setup berhasil' : 'Setup gagal') . '</h2><ul><li>' . implode('</li><li>', $log) . '</li></ul>';
if (!$ok) echo '<p>Periksa: PostgreSQL sedang berjalan, ekstensi <code>pdo_pgsql</code> aktif, dan user/password di <code>config.php</code> benar.</p>';
