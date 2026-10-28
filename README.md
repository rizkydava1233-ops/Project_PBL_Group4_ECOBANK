# EcoBank - Bank Sampah Digital (PHP + PostgreSQL)

## Struktur
```
ecobank/
  index.html            halaman aplikasi (SPA)
  assets/app.css        tampilan
  assets/app.js         logika front-end (memanggil api.php)
  api.php               titik masuk API (front controller, ~20 baris)
  config.php            pengaturan koneksi database
  setup.php             pembuat database, tabel, dan data awal (jalankan sekali)
  database/schema.sql   skema tabel PostgreSQL
  src/
    bootstrap.php       pengaturan PHP, sesi, polyfill, penangan error
    response.php        out(), fail(), dbg()  (keluaran JSON)
    db.php              koneksi PDO db() dan helper q()
    auth.php            me(), need()  (login dan hak akses per peran)
    helpers.php         SLOTS, rates(), points(), img(), pk(), cs()
    routes.php          tabel rute: ?a=nama-aksi => fungsi handler
    controllers/
      auth.php          login, register, logout
      state.php         data awal untuk front-end (disaring per peran)
      pickups.php       buat, batal, ambil, berangkat, selesai
      redeems.php       tukar reward, selesaikan penukaran
      users.php         setujui, hapus, reset sandi
      rewards.php       reward dan tarif poin
      posts.php         konten edukasi/info
      media.php         foto setoran dan bukti
      reports.php       ekspor CSV bulanan
```

## Menambah endpoint baru
1. Tulis fungsi `nama_fungsi($in, $id)` di file yang sesuai di `src/controllers/`.
2. Daftarkan di `src/routes.php`, mis. `'pickup.xyz' => 'pickup_xyz'`.

Front-end tidak perlu diubah: URL tetap `api.php?a=nama-aksi`.

## Menjalankan di Laragon
1. Salin folder ini ke `C:\laragon\www\ecobank`.
2. Pastikan PostgreSQL berjalan dan ekstensi PHP `pdo_pgsql` aktif
   (Laragon: Menu > PHP > Extensions > centang `pdo_pgsql`, lalu restart Apache).
3. Sesuaikan `config.php` (host, port, user, password PostgreSQL).
4. Buka `http://localhost/ecobank/setup.php` sekali. Ini membuat database `ecobank`, tabel, dan data awal,
   lalu membuat file `setup.lock`.
5. Buka `http://localhost/ecobank/` dan masuk.

## Akun demo
| Peran | Email | Sandi |
|---|---|---|
| Admin | admin@eco.id | admin123 |
| Nasabah | nasabah@eco.id | 123456 |
| Petugas | petugas@eco.id | 123456 |

## Aturan yang dijaga di server
- Sandi di-hash dengan `password_hash`; sesi httponly + SameSite; setiap POST wajib header `X-Requested-With`.
- Hak akses per peran dicek di setiap endpoint. Semua query memakai prepared statement.
- Poin dihitung di database dari setoran selesai dikurangi penukaran, bukan dipercaya dari browser.
- Penukaran memakai transaksi dan kunci baris, jadi stok dan saldo aman dari transaksi bersamaan.
- Pengambilan tugas bersifat atomik: hanya satu petugas yang bisa mengambil satu permintaan.
- Foto divalidasi (JPEG, dibatasi ukurannya) dan hanya bisa dilihat pihak terkait.
- Pendaftaran petugas berstatus "menunggu" sampai disetujui admin.

## Sebelum dipakai sungguhan
- Ganti sandi akun demo, atau hapus akunnya. Hapus tombol akun demo di `assets/app.js` (fungsi `auth`).
- Hapus `setup.php` setelah selesai.
- Gunakan HTTPS, dan set `session.cookie_secure=1` di php.ini.
- Foto disimpan di database dalam ukuran kecil. Untuk volume besar, pindahkan ke penyimpanan file.

## PWA (aplikasi di HP)
File tambahan: `manifest.json`, `sw.js`, ikon di `assets/`. Syarat: situs diakses lewat **HTTPS** (atau localhost).
- Android (Chrome): menu ⋮ > "Instal aplikasi" / "Tambahkan ke layar utama".
- iPhone (Safari): tombol Bagikan > "Tambah ke Layar Utama".
- Jika mengubah file aplikasi, naikkan `CACHE` di `sw.js` (mis. `ecobank-v2`) agar pengguna mendapat versi baru.
