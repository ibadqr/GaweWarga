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

// Mengambil data user dari database
$session_user = $_SESSION['user'];
$user_id = isset($session_user['id']) ? $session_user['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postingan_id = isset($_POST['postingan_id']) ? intval($_POST['postingan_id']) : 0;

    try {
        // Cek kepemilikan postingan sebelum dihapus
        $checkQuery = "SELECT warga_id FROM posts WHERE id = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$postingan_id]);
        $post = $checkStmt->fetch();

        if (!$post) {
            header("Location: ../index.php");
            exit;
        }

        if ($post['warga_id'] != $user_id) {
            // Jika mencoba hapus postingan orang lain lewat modifikasi inspect element
            header("Location: ../index.php?status=unauthorized");
            exit;
        }

        // Jalankan Hapus (karena ON DELETE CASCADE di DB Anda, komentar & likes otomatis ikut terhapus aman)
        $deleteQuery = "DELETE FROM posts WHERE id = ?";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->execute([$postingan_id]);

        // Lempar status deleted (SweetAlert Sukses Terhapus)
        header("Location: ../index.php?status=deleted");
        exit;

    } catch (PDOException $e) {
        die("Gagal menghapus postingan: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit;
}