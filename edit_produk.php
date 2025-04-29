<?php
session_start();
include '../config/koneksi.php';

// Validasi session admin
if (!isset($_SESSION['admin'])) {
    header("Location: ../admin/loginresgister.php");
    exit;
}

// Validasi parameter ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../admin/produk.php");
    exit;
}

$id = (int)$_GET['id'];
$query = "SELECT * FROM produk WHERE id_produk=?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: produk.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi input
    $nama = htmlspecialchars(trim($_POST['nama']));
    $harga = (int)$_POST['harga'];
    
    if (empty($nama) || $harga <= 0) {
        $error = "Nama produk dan harga harus diisi dengan benar";
    } else {
        // Handle file upload
        if (!empty($_FILES['gambar']['name'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['gambar']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $gambar = uniqid() . '_' . basename($_FILES['gambar']['name']);
                $target_file = "../css/" . $gambar;
                
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                    // Delete old image if exists
                    if (!empty($data['gambar']) && file_exists("../assets/" . $data['gambar'])) {
                        unlink("../css/" . $data['gambar']);
                    }
                    $query = "UPDATE produk SET nama_produk=?, harga=?, gambar=? WHERE id_produk=?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sisi", $nama, $harga, $gambar, $id);
                } else {
                    $error = "Gagal mengupload gambar";
                }
            } else {
                $error = "Format gambar tidak didukung (hanya JPEG, PNG, GIF)";
            }
        } else {
            $query = "UPDATE produk SET nama_produk=?, harga=? WHERE id_produk=?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sii", $nama, $harga, $id);
        }
        
        if (!isset($error)) {
            if (mysqli_stmt_execute($stmt)) {
                header("Location: ../admin/produk.php");
                exit;
            } else {
                $error = "Gagal memperbarui produk";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <style>
        .error { color: red; margin-bottom: 10px; }
        form { max-width: 500px; margin: 20px auto; }
        input, button { display: block; width: 100%; margin-bottom: 10px; padding: 8px; }
        img { margin: 10px 0; }
    </style>
</head>
<body>
    <h2>Edit Produk</h2>
    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="nama" value="<?= htmlspecialchars($data['nama_produk']) ?>" required>
        <input type="number" name="harga" value="<?= $data['harga'] ?>" min="1" required>
        <input type="file" name="gambar" accept="image/*">
        <img src="../assets/<?= htmlspecialchars($data['gambar']) ?>" width="100">
        <button type="submit">Update</button>
    </form>
</body>
</html>