<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

// 1. Proteksi Halaman: Jika belum login, tendang ke login.php
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Mengambil data session awal
$session_user = $_SESSION['user']; 
$user_id = isset($session_user['id']) ? $session_user['id'] : 0;

// Ambil ulang data profil lengkap user dari database agar tidak memicu Undefined Index
try {
    $stmtUser = $pdo->prepare("SELECT * FROM profil_warga WHERE id = ?");
    $stmtUser->execute([$user_id]);
    $user = $stmtUser->fetch();
    
    if (!$user) {
        header("Location: actions/auth_logout.php");
        exit;
    }
} catch (PDOException $e) {
    die("Gagal mengambil data pengguna: " . $e->getMessage());
}

// 2. Ambil data postingan + nama warga + hitung total likes & komentar dari database[cite: 1]
try {
    $query = "SELECT p.*, w.nama_lengkap,
              (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) as total_likes,
              (SELECT COUNT(*) FROM komentar k WHERE k.post_id = p.id) as total_komentar,
              (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND warga_id = ?) as sudah_like
              FROM posts p 
              JOIN profil_warga w ON p.warga_id = w.id 
              ORDER BY p.id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $postingan = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data postingan: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GaweWarga — Forum Digital RW 02</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 CDN untuk Pop-up Alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome Pro Kit Icons -->
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] antialiased text-slate-900 min-h-screen">

    <!-- NAVBAR UTAMA -->
    <nav class="bg-emerald-600 text-white shadow-sm sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 py-3.5 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="bg-white text-emerald-600 font-extrabold text-xl w-9 h-9 flex items-center justify-center rounded-xl shadow-inner">
                    G
                </div>
                <div>
                    <h1 class="font-bold text-base tracking-tight leading-none">GaweWarga</h1>
                    <p class="text-[10px] text-emerald-100 mt-0.5 tracking-wide uppercase font-semibold">Forum Digital RW 02</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-emerald-700/50 backdrop-blur px-3 py-1.5 rounded-xl border border-emerald-500/30 hidden sm:flex items-center gap-2 text-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                    <span><strong><?php echo htmlspecialchars($user['nama_lengkap']); ?></strong></span>
                    <span class="text-emerald-200 text-[10px] bg-emerald-800/60 px-2 py-0.5 rounded-md uppercase font-bold"><?php echo ucfirst($user['role']); ?></span>
                </div>
                <button onclick="konfirmasiLogout()" class="text-xs bg-rose-500 hover:bg-rose-600 px-4 py-2 rounded-xl font-semibold transition duration-200 shadow-sm cursor-pointer flex items-center gap-1.5">
                    <i class="fad fa-sign-out-alt"></i> Keluar
                </button>
            </div>
        </div>
    </nav>

    <!-- TATA LETAK GRID UTAMA -->
    <main class="max-w-6xl mx-auto px-4 py-6 grid grid-cols-1 md:grid-cols-4 gap-6">
        
        <!-- SIDEBAR KIRI: INFORMASI USER -->
        <aside class="md:col-span-1 hidden md:block">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm sticky top-24">
                <div class="flex flex-col items-center text-center pb-4 border-b border-slate-100">
                    <div class="w-16 h-16 bg-gradient-to-tr from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-md mb-3">
                        <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                    </div>
                    <h3 class="font-bold text-sm text-slate-800 break-all"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h3>
                    <p class="text-xs text-slate-400 mt-0.5">Warga Aktif</p>
                </div>
                
                <div class="mt-4 space-y-1 text-xs text-slate-600">
                    <div class="flex justify-between p-2 rounded-lg hover:bg-slate-50">
                        <span><i class="far fa-map-marker-alt mr-1.5 text-slate-400"></i> Wilayah</span>
                        <span class="font-bold text-slate-800"><?php echo htmlspecialchars($user['kode_rw']); ?></span>
                    </div>
                    <div class="flex justify-between p-2 rounded-lg hover:bg-slate-50">
                        <span><i class="far fa-user-shield mr-1.5 text-slate-400"></i> Peran Akun</span>
                        <span class="font-semibold text-emerald-600 uppercase text-[10px] bg-emerald-50 px-2 py-0.5 rounded"><?php echo htmlspecialchars($user['role']); ?></span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- AREA UTAMA: FORM & FEED POSTINGAN -->
        <section class="col-span-1 md:col-span-2 space-y-5">

            <!-- BOX BUAT POSTINGAN -->
            <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80">
                <div class="flex gap-3 items-start mb-4">
                    <div class="w-9 h-9 bg-emerald-100 text-emerald-700 font-bold text-sm flex items-center justify-center rounded-xl shrink-0">
                        <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                    </div>
                    <div class="w-full">
                        <h3 class="font-bold text-xs text-slate-700 uppercase tracking-wider">Bagikan Kabar Baru</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Sampaikan informasi resmi atau usulan untuk kebaikan RW.</p>
                    </div>
                </div>

                <form action="actions/post_create.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5 tracking-wider">Pilih Kategori Info</label>
                        <select name="kategori" class="w-full md:w-auto border border-slate-200 rounded-xl px-3 py-2 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition cursor-pointer font-medium text-slate-700" required>
                            <option value="umum">📋 Umum</option>
                            <option value="acara">🗓️ Acara Warga</option>
                            <option value="kabar_duka">🕊️ Kabar Duka</option>
                            <option value="keamanan">🛡️ Info Keamanan</option>
                            <option value="aspirasi">💬 Aspirasi / Keluhan</option>
                        </select>
                    </div>

                    <textarea name="konten" rows="3" placeholder="Apa yang ingin Anda sampaikan hari ini, warga?" class="w-full border border-slate-200 rounded-xl p-3.5 text-sm bg-slate-50 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition resize-none leading-relaxed" required></textarea>
                    
                    <div class="flex justify-end pt-1">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-5 py-2.5 rounded-xl transition shadow-md hover:shadow-emerald-600/10 cursor-pointer flex items-center gap-1.5">
                            <i class="far fa-paper-plane"></i> Kirim Postingan
                        </button>
                    </div>
                </form>
            </div>

            <!-- FEED TIMELINE -->
            <div class="space-y-4">
                <div class="flex justify-between items-center px-1">
                    <h2 class="font-bold text-slate-500 text-xs uppercase tracking-wider">Linimasa Warga</h2>
                </div>

                <?php if (empty($postingan)): ?>
                    <div class="bg-white rounded-2xl p-10 text-center border border-slate-200/80 text-slate-400 text-sm shadow-inner">
                        <div class="text-3xl mb-2"><i class="fad fa-folders text-slate-300"></i></div>
                        Belum ada info yang dibagikan. Jadilah yang pertama memulai obrolan!
                    </div>
                <?php else: ?>
                    <?php foreach ($postingan as $row): ?>
                        <article class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 transition hover:border-slate-300 flex flex-col justify-between">
                            
                            <!-- Header Post: Profil & Tombol Manajemen -->
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex gap-3 items-center">
                                    <div class="w-9 h-9 bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center rounded-xl shadow-sm">
                                        <?php echo strtoupper(substr($row['nama_lengkap'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="font-bold text-slate-800 text-sm leading-snug"><?php echo htmlspecialchars($row['nama_lengkap']); ?></h4>
                                            
                                            <?php 
                                                $badgeColor = 'bg-slate-100 text-slate-600';
                                                if ($row['kategori'] === 'keamanan') $badgeColor = 'bg-amber-50 text-amber-700 border border-amber-200/60';
                                                if ($row['kategori'] === 'kabar_duka') $badgeColor = 'bg-purple-50 text-purple-700 border border-purple-200/60';
                                                if ($row['kategori'] === 'acara') $badgeColor = 'bg-blue-50 text-blue-700 border border-blue-200/60';
                                                if ($row['kategori'] === 'aspirasi') $badgeColor = 'bg-rose-50 text-rose-700 border border-rose-200/60';
                                            ?>
                                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wide <?php echo $badgeColor; ?>">
                                                <?php echo str_replace('_', ' ', htmlspecialchars($row['kategori'])); ?>
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Wilayah Anggota: <?php echo htmlspecialchars($row['kode_rw']); ?></p>
                                    </div>
                                </div>

                                <!-- Aksi Edit/Hapus khusus Pemilik[cite: 1] -->
                                <?php if ($row['warga_id'] == $user['id']): ?>
                                    <div class="flex items-center gap-2 bg-slate-50 px-2.5 py-1 rounded-xl border border-slate-100">
                                        <button onclick="bukaModalEdit(<?php echo $row['id']; ?>, '<?php echo addslashes(htmlspecialchars($row['kategori'])); ?>', '<?php echo addslashes(htmlspecialchars($row['konten'])); ?>')" class="text-blue-600 hover:text-blue-700 text-xs font-semibold cursor-pointer" title="Ubah postingan">
                                            <i class="far fa-edit"></i>
                                        </button>
                                        <span class="text-slate-300 text-[10px]">•</span>
                                        <button onclick="konfirmasiHapus(<?php echo $row['id']; ?>)" class="text-rose-500 hover:text-rose-600 text-xs font-semibold cursor-pointer" title="Hapus postingan">
                                            <i class="far fa-trash-alt"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Isi Konten Text Utama -->
                            <div class="text-slate-700 text-sm leading-relaxed whitespace-pre-line break-words pl-0.5 mb-4">
                                <?php echo htmlspecialchars($row['konten']); ?>
                            </div>

                            <!-- Toolbar Aksi Sosial (Like & Komen)[cite: 1] -->
                            <div class="border-t border-slate-100 pt-3 mt-1 flex flex-col gap-3">
                                <div class="flex gap-2 text-xs font-medium text-slate-500">
                                    <!-- Tombol Like[cite: 1] -->
                                    <a href="actions/post_like.php?post_id=<?php echo $row['id']; ?>" class="flex items-center gap-1.5 py-1.5 px-3 rounded-xl transition <?php echo $row['sudah_like'] > 0 ? 'bg-rose-50 text-rose-600 font-bold' : 'hover:bg-slate-50 hover:text-slate-800'; ?>">
                                        <i class="<?php echo $row['sudah_like'] > 0 ? 'fas fa-heart text-rose-500' : 'far fa-heart'; ?>"></i> 
                                        <span>Suka (<?php echo $row['total_likes']; ?>)</span>
                                    </a>
                                    
                                    <!-- Tombol Komentar[cite: 1] -->
                                    <button onclick="toggleKomentar(<?php echo $row['id']; ?>)" class="flex items-center gap-1.5 py-1.5 px-3 rounded-xl hover:bg-slate-50 hover:text-slate-800 transition cursor-pointer">
                                        <i class="far fa-comment-alt-lines"></i> 
                                        <span>Balasan (<?php echo $row['total_komentar']; ?>)</span>
                                    </button>
                                </div>

                                <!-- PANEL SUB-KOMENTAR[cite: 1] -->
                                <div id="box-komentar-<?php echo $row['id']; ?>" class="hidden bg-slate-50/70 p-4 rounded-2xl border border-slate-200/60 space-y-3 mt-1">
                                    
                                    <!-- Daftar List Balasan Warga[cite: 1] -->
                                    <div class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
                                        <?php
                                        $stmtKom = $pdo->prepare("SELECT k.*, w.nama_lengkap FROM komentar k JOIN profil_warga w ON k.warga_id = w.id WHERE k.post_id = ? ORDER BY k.id ASC");
                                        $stmtKom->execute([$row['id']]);
                                        $list_komentar = $stmtKom->fetchAll();
                                        
                                        if(empty($list_komentar)):
                                        ?>
                                            <p class="text-xs text-slate-400 italic text-center py-2">Belum ada tanggapan warga.</p>
                                        <?php else: ?>
                                            <?php foreach($list_komentar as $kom): ?>
                                                <div class="text-xs bg-white p-3 rounded-xl border border-slate-100 shadow-sm leading-relaxed">
                                                    <div class="flex justify-between items-center mb-1">
                                                        <strong class="text-slate-800"><?php echo htmlspecialchars($kom['nama_lengkap']); ?></strong>
                                                        <span class="text-[9px] text-slate-400 font-medium">Warga</span>
                                                    </div>
                                                    <span class="text-slate-600"><?php echo htmlspecialchars($kom['konten']); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Form Tulis Komentar Baru (Diberikan Padding Jarak Agar Tidak Mepet Garis di Mobile)[cite: 1] -->
                                    <form action="actions/post_comment.php" method="POST" class="flex gap-2 items-center mt-4 border-t border-slate-200/60 pt-4 pb-1">
                                        <input type="hidden" name="post_id" value="<?php echo $row['id']; ?>">
                                        <input type="text" name="konten" placeholder="Ketik tanggapan Anda..." class="flex-1 border border-slate-200 rounded-xl px-3 py-2 text-xs bg-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" required>
                                        
                                        <!-- Tombol Pesawat Kertas Premium Pro -->
                                        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white w-8 h-8 flex items-center justify-center rounded-xl transition shadow-sm cursor-pointer shrink-0" title="Kirim Balasan">
                                            <i class="fas fa-paper-plane text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- SIDEBAR KANAN: WIDGET/DASHBOARD INFORMASI -->
        <aside class="md:col-span-1 hidden md:block">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm space-y-4 sticky top-24">
                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-400"><i class="far fa-bullhorn mr-1"></i> Papan Pengumuman</h4>
                    <p class="text-[11px] text-slate-500 mt-2 leading-relaxed">Gunakan fitur <strong>GaweWarga</strong> dengan bijak untuk koordinasi internal rukun warga yang kondusif.</p>
                </div>
                <hr class="border-slate-100">
                <div class="text-[11px] text-slate-400 font-medium">
                    &copy; 2026 Pengurus RW 02.
                </div>
            </div>
        </aside>
    </main>

    <!-- MODAL POP-UP EDIT POSTINGAN -->
    <div id="modalEdit" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden backdrop-blur-md bg-slate-900/40 transition-all duration-300">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden border border-slate-100 transform scale-95 transition-transform duration-200" id="modalBox">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-sm"><i class="far fa-edit text-slate-500 mr-1.5"></i> Perbarui Postingan Anda</h3>
                <button onclick="tutupModalEdit()" class="text-slate-400 hover:text-slate-600 text-xl font-medium cursor-pointer transition">&times;</button>
            </div>
            
            <form action="actions/post_edit.php" method="POST" class="p-5 space-y-4">
                <input type="hidden" name="postingan_id" id="modalPostId">
                
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Kategori Kunci</label>
                    <select name="kategori" id="modalPostKategori" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        <option value="umum">📢 Umum</option>
                        <option value="acara">🗓️ Acara Warga</option>
                        <option value="kabar_duka">🕊️ Kabar Duka</option>
                        <option value="keamanan">🛡️ Info Keamanan</option>
                        <option value="aspirasi">💬 Aspirasi / Keluhan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Ubah Deskripsi Konten</label>
                    <textarea name="konten" id="modalPostKonten" rows="4" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none leading-relaxed" required></textarea>
                </div>
                
                <div class="pt-2 flex justify-end gap-2 text-xs font-semibold">
                    <button type="button" onclick="tutupModalEdit()" class="px-4 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-xl transition cursor-pointer">
                        Batalkan
                    </button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 rounded-xl transition shadow-md cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIK INTERAKSI & ALERT -->
    <script>
        var modal = document.getElementById('modalEdit');
        var modalBox = document.getElementById('modalBox');
        var inputId = document.getElementById('modalPostId');
        var inputKategori = document.getElementById('modalPostKategori');
        var inputKonten = document.getElementById('modalPostKonten');

        function bukaModalEdit(id, kategori, konten) {
            inputId.value = id;
            inputKonten.value = konten;
            inputKategori.value = kategori; 

            modal.classList.remove('hidden');
            setTimeout(function() {
                modalBox.style.transform = 'scale(1)';
            }, 10);
        }

        function tutupModalEdit() {
            modalBox.style.transform = 'scale(0.95)';
            setTimeout(function() {
                modal.classList.add('hidden');
            }, 150);
        }

        // Buka/Tutup Box Panel Komentar[cite: 1]
        function toggleKomentar(postId) {
            var box = document.getElementById('box-komentar-' + postId);
            if (box.classList.contains('hidden')) {
                box.classList.remove('hidden');
            } else {
                box.classList.add('hidden');
            }
        }

        // Alert Notifikasi dari URL Params menggunakan SweetAlert2
        window.addEventListener('DOMContentLoaded', function() {
            var urlParams = new URLSearchParams(window.location.search);
            var status = urlParams.get('status');

            if (status) {
                window.history.replaceState({}, document.title, window.location.pathname);

                if (status === 'deleted') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Dihapus',
                        text: 'Postingan Anda telah dihapus secara permanen dari linimasa.',
                        confirmButtonColor: '#059669'
                    });
                } else if (status === 'updated') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Diperbarui',
                        text: 'Perubahan konten postingan Anda berhasil disimpan!',
                        confirmButtonColor: '#059669'
                    });
                } else if (status === 'unauthorized') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Akses Ditolak',
                        text: 'Anda tidak memiliki hak otoritas atas postingan ini.',
                        confirmButtonColor: '#dc2626'
                    });
                } else if (status === 'empty') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Gagal Mengirim',
                        text: 'Konten deskripsi postingan wajib diisi.',
                        confirmButtonColor: '#d97706'
                    });
                }
            }
        });

        function konfirmasiHapus(postId) {
            Swal.fire({
                title: 'Hapus Postingan?',
                text: "Seluruh data komentar dan like di postingan ini akan hilang permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'actions/post_delete.php';

                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'postingan_id';
                    input.value = postId;

                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function konfirmasiLogout() {
            Swal.fire({
                title: 'Keluar Aplikasi?',
                text: "Anda harus login kembali untuk mengakses linimasa.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'actions/auth_logout.php';
                }
            });
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                tutupModalEdit();
            }
        }
    </script>
</body>
</html>