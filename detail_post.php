<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$post_id = $_GET['id'] ?? null;

// 1. Ambil data postingan utama (pastikan terisolasi dengan kode_rw user)
$post_query = "SELECT p.*, w.nama_lengkap,
               (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as total_likes,
               EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND warga_id = :current_warga_id) as sudah_like
               FROM posts p
               JOIN profil_warga w ON p.warga_id = w.id
               WHERE p.id = :post_id AND p.kode_rw = :kode_rw";

$stmt = $pdo->prepare($post_query);
$stmt->execute([
    'post_id' => $post_id,
    'kode_rw' => $user['kode_rw'],
    'current_warga_id' => $user['id']
]);
$post = $stmt->fetch();

// Jika post tidak ditemukan atau beda RW, tendang balik ke halaman utama
if (!$post) {
    header("Location: index.php");
    exit;
}

// 2. Ambil daftar komentar untuk postingan ini
$comment_query = "SELECT k.*, w.nama_lengkap 
                  FROM komentar k
                  JOIN profil_warga w ON k.warga_id = w.id
                  WHERE k.post_id = :post_id
                  ORDER BY k.created_at ASC";
$stmt = $pdo->prepare($comment_query);
$stmt->execute(['post_id' => $post_id]);
$comments = $stmt->fetchAll();
?>

<main class="max-w-md mx-auto px-4 mt-4 space-y-4">
    
    <!-- Tombol Kembali -->
    <a href="index.php" class="inline-flex items-center text-xs font-semibold text-emerald-600 gap-1 hover:underline">
        ⬅️ Kembali ke Forum
    </a>

    <!-- POST UTAMA CARD -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 space-y-3">
        <div class="flex justify-between items-start">
            <div>
                <h4 class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($post['nama_lengkap']); ?></h4>
                <span class="text-[10px] text-slate-400"><?php echo date('d M Y, H:i', strtotime($post['created_at'])); ?></span>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 uppercase">
                📢 <?php echo htmlspecialchars($post['kategori']); ?>
            </span>
        </div>

        <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line"><?php echo htmlspecialchars($post['konten']); ?></p>

        <div class="pt-2 border-t border-slate-100 text-slate-500 text-xs">
            <a href="actions/post_like.php?id=<?php echo $post['id']; ?>" class="flex items-center gap-1 <?php echo $post['sudah_like'] ? 'text-red-500 font-bold' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="<?php echo $post['sudah_like'] ? '#ef4444' : 'none'; ?>" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
                <span><?php echo $post['total_likes']; ?> Suka</span>
            </a>
        </div>
    </div>

    <!-- SEKSI KOMENTAR/BALASAN -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold text-slate-500 tracking-wide uppercase">💬 Balasan Tetangga (<?php echo count($comments); ?>)</h3>
        
        <?php if (empty($comments)): ?>
            <p class="text-xs italic text-slate-400 pl-2">Belum ada tanggapan. Jadilah yang pertama memberikan balasan!</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($comments as $comment): ?>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-1">
                        <div class="flex justify-between items-center">
                            <strong class="text-xs text-slate-700"><?php echo htmlspecialchars($comment['nama_lengkap']); ?></strong>
                            <span class="text-[9px] text-slate-400"><?php echo date('H:i', strtotime($comment['created_at'])); ?></span>
                        </div>
                        <p class="text-sm text-slate-600"><?php echo htmlspecialchars($comment['konten']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- FORM INPUT BALASAN BARU -->
    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-inner">
        <form action="actions/comment_add.php" method="POST" class="flex gap-2">
            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
            <input type="text" name="konten" required 
                   class="flex-grow text-sm bg-slate-50 border border-slate-300 text-slate-700 rounded-lg p-2 focus:ring-emerald-500 focus:border-emerald-500 placeholder-slate-400" 
                   placeholder="Tulis balasan Anda...">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs px-4 py-2 rounded-lg transition pt-2">
                Balas
            </button>
        </form>
    </div>

</main>

<?php require_once 'includes/footer.php'; ?>