<?php
// =============================================
// KONFIGURASI DATABASE & SISTEM
// =============================================

// Cegah multiple include
if (defined('DB_LOADED')) {
    return;
}
define('DB_LOADED', true);

$host = 'localhost';
$dbname = 'catering_db';
$user = 'root';
$pass = '';

// NAMA SISTEM (untuk ditampilkan di semua halaman)
define('SISTEM_NAME', 'Cateringku');
define('SISTEM_SHORT', 'Cateringku');
define('SISTEM_TAGLINE', 'Solusi Catering untuk Setiap Acara Anda');

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Fungsi rp dengan pengecekan
if (!function_exists('rp')) {
    function rp($angka) {
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}
?>