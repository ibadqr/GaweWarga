<?php
// Pastikan session sudah berjalan
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Proteksi halaman: Jika belum login, tendang ke login.php
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GaweWarga - Forum Informasi</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans antialiased text-slate-800 pb-20">

    <!-- FIXED NAVBAR (Tidak transparan, selalu terlihat saat scroll) -->
    <header class="sticky top-0 z-50 bg-emerald-600 text-white shadow-md px-4 py-3">
        <div class="max-w-md mx-auto flex justify-between items-center">
            <div>
                <a href="index.php" class="text-lg font-bold tracking-wide block">GaweWarga</a>
                <p class="text-xs text-emerald-100 font-medium">Forum Informasi Lingkungan</p>
            </div>
            <!-- Identitas RW Dinamis & Tombol Keluar -->
            <div class="flex items-center gap-2">
                <span class="bg-emerald-700 text-emerald-100 text-xs font-bold px-2.5 py-1 rounded-full border border-emerald-500">
                    📌 <?php echo htmlspecialchars($user['kode_rw']); ?>
                </span>
                <a href="actions/auth_logout.php" onclick="return confirm('Yakin ingin keluar?')" class="text-xs bg-red-600 hover:bg-red-700 text-white font-medium px-2 py-1 rounded-lg transition text-[10px]">
                    Keluar
                </a>
            </div>
        </div>
    </header>
