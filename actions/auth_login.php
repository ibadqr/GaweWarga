<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        // 1. Ambil data warga berdasarkan username
        $stmt = $pdo->prepare("SELECT * FROM profil_warga WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $warga = $stmt->fetch();

        // 2. Verifikasi keamanan password (Membaca hash dari database)
        if ($warga && password_verify($password, $warga['password'])) {
            
            // 3. Simpan data ke session untuk otentikasi hak akses RW
            $_SESSION['user'] = [
                'id' => $warga['id'],
                'nama' => $warga['nama_lengkap'],
                'kode_rw' => $warga['kode_rw'],
                'role' => $warga['role']
            ];
            
            // 4. Redirect ke halaman utama
            header("Location: ../index.php");
            exit;
        } else {
            // Jika password salah / user tidak ketemu
            header("Location: ../login.php?error=1");
            exit;
        }
    } catch (PDOException $e) {
        die("Eror sistem login: " . $e->getMessage());
    }
} else {
    header("Location: ../login.php");
    exit;
}