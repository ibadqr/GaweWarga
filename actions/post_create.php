<?php
session_start();
require_once '../config/database.php';

// Proteksi: Pastikan hanya user yang sudah login yang bisa memposting
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $warga_id = $_SESSION['user']['id'];
    $kode_rw  = $_SESSION['user']['kode_rw']; // Kunci teritorial otomatis dari session
    $kategori = $_POST['kategori'];
    $konten   = trim($_POST['konten']);

    // Validasi konten tidak boleh kosong
    if (empty($konten)) {
        header("Location: ../index.php?msg=kosong");
        exit;
    }

    try {
        // Insert data postingan baru
        $query = "INSERT INTO posts (warga_id, kode_rw, kategori, konten) VALUES (:warga_id, :kode_rw, :kategori, :konten)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'warga_id' => $warga_id,
            'kode_rw'  => $kode_rw,
            'kategori' => $kategori,
            'konten'   => $konten
        ]);

        header("Location: ../index.php?msg=sukses");
        exit;
    } catch (PDOException $e) {
        die("Gagal menyimpan kiriman: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit;
}