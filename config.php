<?php
// Sesuaikan dengan PostgreSQL di Laragon kamu
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '5432');
define('DB_NAME', 'ecobank_php');
define('DB_USER', 'postgres');
define('DB_PASS', 'dava1233');
date_default_timezone_set('Asia/Jakarta');
function db_dsn($name = DB_NAME) { return 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . $name; }
