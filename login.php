<?php
session_start();
// Jika sudah login, langsung alihkan ke halaman utama
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - GaweWarga</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans antialiased text-slate-800 flex flex-col min-h-screen">

    <!-- FIXED NAVBAR -->
    <header class="sticky top-0 z-50 bg-emerald-600 text-white shadow-md px-4 py-3">
        <div class="max-w-md mx-auto text-center">
            <h1 class="text-lg font-bold tracking-wide">GaweWarga</h1>
            <p class="text-xs text-emerald-100 font-medium">Platform Informasi & Komunitas Warga</p>
        </div>
    </header>

    <!-- FORM CONTAINER -->
    <main class="flex-grow flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-6">
            <div class="text-center space-y-1">
                <h2 class="text-xl font-bold text-slate-800">Selamat Datang</h2>
                <p class="text-sm text-slate-400">Silakan masuk menggunakan akun warga Anda</p>
            </div>

            <!-- Pesan Error jika Login Gagal -->
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 text-xs p-3 rounded-xl">
                    ⚠️ Username atau password salah. Silakan coba lagi.
                </div>
            <?php endif; ?>

            <form action="actions/auth_login.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Username</label>
                    <input type="text" name="username" required 
                           class="w-full text-sm bg-slate-50 border border-slate-300 text-slate-700 rounded-xl p-3 focus:ring-emerald-500 focus:border-emerald-500 placeholder-slate-400" 
                           placeholder="Masukkan username Anda">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Password</label>
                    <input type="password" name="password" required 
                           class="w-full text-sm bg-slate-50 border border-slate-300 text-slate-700 rounded-xl p-3 focus:ring-emerald-500 focus:border-emerald-500 placeholder-slate-400" 
                           placeholder="••••••••">
                </div>

                <button type="submit" 
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm py-3 px-4 rounded-xl shadow transition duration-200 pt-3">
                    Masuk Sekarang
                </button>
            </form>

            <!-- Info Akun Dummy untuk Uji Coba -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-[11px] text-amber-800 space-y-1">
                <p class="font-bold">💡 Akun Uji Coba (Database Seeder):</p>
                <p>• Username: <strong>eko</strong> (RW-02) | Password: <strong>password_123</strong></p>
                <p>• Username: <strong>sri</strong> (RW-02 - Admin) | Password: <strong>password_123</strong></p>
                <p>• Username: <strong>doni</strong> (RW-03) | Password: <strong>password_123</strong></p>
            </div>
        </div>
    </main>

</body>
</html>