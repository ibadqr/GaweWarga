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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $konten = isset($_POST['konten']) ? trim($_POST['konten']) : '';

    if (!empty($konten) && $post_id > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO komentar (post_id, warga_id, konten) VALUES (?, ?, ?)");
            $stmt->execute([$post_id, $user_id, $konten]);
        } catch (PDOException $e) {
            die("Gagal mengirim komentar: " . $e->getMessage());
        }
    }
}

header("Location: ../index.php");
exit;