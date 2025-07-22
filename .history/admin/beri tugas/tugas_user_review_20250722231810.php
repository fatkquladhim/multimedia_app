<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!isset($_GET['id_tugas']) || !is_numeric($_GET['id_tugas'])) {
    header('Location: tugas_selesai_riwayat.php?status=error&message=ID tugas tidak valid.');
    exit;
}

$id_tugas = $_GET['id_tugas'];

// Ambil detail tugas dan jawaban
$query = "SELECT 
    t.id as tugas_id,
    t.judul,
    t.deskripsi,
    t.deadline,
    t.status as tugas_status,
    u.username as penerima_username,
    u.nama_lengkap as penerima_nama_lengkap,
    tj.id as id_jawaban,
    tj.file_jawaban,
    tj.nilai,
    tj.komentar,
    tj.tanggal_submit
FROM tugas t
LEFT JOIN users u ON t.id_penerima_tugas = u.id
LEFT JOIN tugas_jawaban tj ON t.id = tj.id_tugas
WHERE t.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_tugas);
$stmt->execute();
$result = $stmt->get_result();
$tugas = $result->fetch_assoc();

if (!$tugas) {
    include '../header_beckend.php';
    include '../header.php';
    echo '<div class="min-h-screen bg-gradient-to-br from-red-50 to-red-100 py-8">';
    echo '<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">';
    echo '<div class="bg-white rounded-lg shadow-lg p-6 text-center">';
    echo '<svg class="w-16 h-16 text-red-300 mb-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>';
    echo '</svg>';
    echo '<p class="text-red-600 text-lg font-semibold mb-4">Tugas tidak ditemukan.</p>';
    echo '<a href="tugas_selesai_riwayat.php" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200">';
    echo '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>';
    echo '</svg>';
    echo 'Kembali';
    echo '</a>';
    echo '</div></div></div>';
    $stmt->close();
    $conn->close();
    include '../footer.php';
    exit;
}

include '../header_beckend.php';
include '../header.php';
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center space-x-3 mb-2">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Review Tugas</h2>
                    <p class="text-gray-600">Berikan penilaian dan komentar untuk tugas yang telah diselesaikan</p>
                </div>
            </div>
        </div>

        <!-- Task Details Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Tugas</h3>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Judul</label>
                        <p class="text-gray-900 bg-gray-50 p-3 rounded-lg"><?php echo htmlspecialchars($tugas['judul']); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Dikerjakan oleh</label>
                        <div class="flex items-center space-x-2 bg-gray-50 p-3 rounded-lg">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <span class="text-gray-900 font-medium"><?php echo htmlspecialchars($tugas['penerima_username'] . ' (' . ($tugas['penerima_nama_lengkap'] ?? 'Nama tidak ada') . ')'); ?></span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Deadline</label>
                        <div class="flex items-center space-x-2 bg-gray-50 p-3 rounded-lg">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-gray-900"><?php echo date('d/m/Y', strtotime($tugas['deadline'])); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Status Tugas</label>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <?php echo htmlspecialchars($tugas['tugas_status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Submit</label>
                        <div class="flex items-center space-x-2 bg-gray-50 p-3 rounded-lg">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-gray-900"><?php echo $tugas['tanggal_submit'] ? date('d/m/Y H:i', strtotime($tugas['tanggal_submit'])) : '-'; ?></span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">File Jawaban</label>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <?php if($tugas['file_jawaban']): ?>
                                <a href="../../uploads/tugas_jawaban/<?php echo htmlspecialchars($tugas['file_jawaban']); ?>" target="_blank" 
                                   class="inline-flex items-center text-blue-600 hover:text-blue-800 transition duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    Lihat File
                                </a>
                            <?php else: ?>
                                <div class="flex items-center text-red-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    Belum ada file jawaban
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-gray-200">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi Tugas</label>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-900 leading-relaxed"><?php echo nl2br(htmlspecialchars($tugas['deskripsi'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Grading Section -->
        <?php if (empty($tugas['id_jawaban'])): ?>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Jawaban</h3>
                    <p class="text-gray-600 mb-6">Tugas ini belum memiliki jawaban dari user. Tidak dapat memberikan nilai.</p>
                    <a href="tugas_selesai_riwayat.php" 
                       class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali ke Riwayat Tugas
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Penilaian Tugas</h3>
                    <p class="text-gray-600">Berikan nilai dan komentar untuk tugas yang telah diselesaikan</p>
                </div>
                
                <form action="tugas_user_review_store.php" method="POST" class="space-y-6">
                    <input type="hidden" name="id_jawaban" value="<?php echo $tugas['id_jawaban']; ?>">
                    <input type="hidden" name="id_tugas" value="<?php echo $tugas['tugas_id']; ?>">
                    
                    <!-- Current Grade Display (if exists) -->
                    <?php if($tugas['nilai'] !== null): ?>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-blue-800 font-medium">Tugas sudah dinilai</h4>
                                    <p class="text-blue-700">Nilai saat ini: <strong><?php echo htmlspecialchars($tugas['nilai']); ?></strong></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Grade Input -->
                    <div>
                        <label for="nilai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nilai (0-100) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="number" 
                                id="nilai" 
                                name="nilai" 
                                min="0" 
                                max="100" 
                                value="<?php echo htmlspecialchars($tugas['nilai'] ?? ''); ?>" 
                                required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 placeholder-gray-400"
                                placeholder="Masukkan nilai 0-100"
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-sm">/100</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Comments -->
                    <div>
                        <label for="komentar" class="block text-sm font-semibold text-gray-700 mb-2">
                            Komentar
                        </label>
                        <textarea 
                            id="komentar" 
                            name="komentar" 
                            rows="6" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 placeholder-gray-400 resize-vertical"
                            placeholder="Berikan komentar atau feedback untuk tugas ini..."
                        ><?php echo htmlspecialchars($tugas['komentar'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6 border-t border-gray-200">
                        <a href="tugas_selesai_riwayat.php" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Kembali ke Riwayat
                        </a>
                        <button 
                            type="submit" 
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Simpan Nilai
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
$stmt->close();
?>