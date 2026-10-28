<?php
// Koneksi PDO (PostgreSQL) dan helper query berparameter.

function db() {
  static $pdo = null;
  if ($pdo === null) {
    try {
      $pdo = new PDO(db_dsn(), DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
    } catch (Throwable $e) {
      fail('Koneksi database gagal' . dbg($e), 500);
    }
  }
  return $pdo;
}

function q($sql, $p = []) {
  $s = db()->prepare($sql);
  $s->execute($p);
  return $s;
}
