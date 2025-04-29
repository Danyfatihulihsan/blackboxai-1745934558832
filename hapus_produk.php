<?php
session_start();
include '../config/koneksi.php';

// Validasi session admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Validasi parameter ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: produk.php");
    exit;
}

$id = (int)$_GET['id'];

// Dapatkan data produk untuk menghapus gambar
$query = "SELECT gambar FROM produk WHERE id_produk=?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if ($data) {
    // Hapus gambar jika ada
    if (!empty($data['gambar']) && file_exists("../assets/" . $data['gambar'])) {
        unlink("../css/" . $data['gambar']);
    }
    
    // Hapus data dari database
    $query = "DELETE FROM produk WHERE id_produk=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
}

header("Location: ../admin/produk.php");
exit;
?>