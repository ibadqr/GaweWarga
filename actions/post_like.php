<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$session_user = $_SESSION['user'];
$user_id = isset($session_user['id']) ? $session_user['id'] : 0;
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if ($post_id > 0) {
    try {
        // Cek apakah sudah pernah like
        $stmtCheck = $pdo->prepare("SELECT * FROM likes WHERE post_id = ? AND warga_id = ?");
        $stmtCheck->execute([$post_id, $user_id]);
        
        if ($stmtCheck->rowCount() > 0) {
            // Sudah like? Maka Unlike (Hapus data)
            $stmtDel = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND warga_id = ?");
            $stmtDel->execute([$post_id, $user_id]);
        } else {
            // Belum like? Tambahkan data baru
            $stmtIns = $pdo->prepare("INSERT INTO likes (post_id, warga_id) VALUES (?, ?)");
            $stmtIns->execute([$post_id, $user_id]);
        }
    } catch (PDOException $e) {
        die("Gagal memproses like: " . $e->getMessage());
    }
}

// Kembalikan ke halaman utama
header("Location: ../index.php");
exit;