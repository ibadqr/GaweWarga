<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

// Proteksi: Wajib login
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

// Mengambil data user dari database (sesuai Solusi 2 sebelumnya)
$session_user = $_SESSION['user'];
$user_id = isset($session_user['id']) ? $session_user['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postingan_id = isset($_POST['postingan_id']) ? intval($_POST['postingan_id']) : 0;
    $kategori = isset($_POST['kategori']) ? trim($_POST['kategori']) : '';
    $konten = isset($_POST['konten']) ? trim($_POST['konten']) : '';

    // Validasi data kosong
    if (empty($konten) || empty($kategori)) {
        header("Location: ../index.php?status=empty");
        exit;
    }

    try {
        // Cek dulu apakah postingan ini benar-benar milik user yang sedang login
        $checkQuery = "SELECT warga_id FROM posts WHERE id = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$postingan_id]);
        $post = $checkStmt->fetch();

        if (!$post) {
            header("Location: ../index.php");
            exit;
        }

        if ($post['warga_id'] != $user_id) {
            // Jika bukan miliknya, lempar status unauthorized (SweetAlert Merah)
            header("Location: ../index.php?status=unauthorized");
            exit;
        }

        // Jalankan Update jika validasi aman
        $updateQuery = "UPDATE posts SET kategori = ?, konten = ? WHERE id = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$kategori, $konten, $postingan_id]);

        // Lempar status updated (SweetAlert Sukses)
        header("Location: ../index.php?status=updated");
        exit;

    } catch (PDOException $e) {
        die("Gagal memperbarui postingan: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit;
}