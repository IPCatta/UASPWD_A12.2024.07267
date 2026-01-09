<?php
// Skrip hapus data barang versi sederhana

// Mulai session (untuk menampilkan pesan)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke database
require_once 'koneksi.php';

// Ambil ID dari URL dan pastikan berupa angka
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    // Hapus data langsung berdasarkan ID
    $query = "DELETE FROM barang WHERE id = $id";

    if (mysqli_query($koneksi, $query)) {
        $_SESSION['pesan'] = "Barang berhasil dihapus.";
        $_SESSION['tipe']  = "success";
    } else {
        $_SESSION['pesan'] = "Gagal menghapus barang.";
        $_SESSION['tipe']  = "error";
    }
} else {
    $_SESSION['pesan'] = "ID barang tidak valid.";
    $_SESSION['tipe']  = "error";
}

// Kembali ke halaman data barang
header("Location: tampilDataBarang.php");
exit;
?>
