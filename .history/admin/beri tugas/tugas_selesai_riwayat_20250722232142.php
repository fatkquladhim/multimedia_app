<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil daftar tugas yang sudah selesai atau diperiksa
// 'diperiksa' status can be added if you want to show tasks that have been graded
$query = "SELECT 
    t.id,
    t.judul,
    t.deskripsi,
    t.deadline,
    t.status,
    u.username as penerima_username,
    u.nama_lengkap as penerima_nama_lengkap,
    tj.file_jawaban,
    tj.nilai,
    tj.komentar,
    tj.id as id_jawaban -- Get the ID of the answer for review
FROM tugas t
LEFT JOIN users u ON t.id_penerima_tugas = u.id
LEFT JOIN tugas_jawaban tj ON t.id = tj.id_tugas
WHERE t.status IN ('selesai') -- Include 'diperiksa' if you use it
ORDER BY t.deadline DESC";

$result = $conn->query($query);
include '../header_beckend.php';
include '../header.php';
?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Riwayat Tugas Selesai</h2>
                        <p class="text-gray-600">Daftar semua tugas yang telah diselesaikan oleh pengguna</p>
                    </div>
                </div>
                <div class="hidden sm:block">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <?php echo $result->num_rows; ?> Tugas Selesai
                    </span>
                </div>
            </div>
        </div>

        <!-- Status Messages -->
        <?php if (isset($_GET['status'])): ?>
            <div class="mb-6">
                <?php if ($_GET['status'] == 'success'): ?>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="text-green-800 font-medium"><?php echo htmlspecialchars($_GET['message']); ?></div>
                    </div>
                <?php else: ?>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="text-red-800 font-medium"><?php echo htmlspecialchars($_GET['message']); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Table Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Desktop Table -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Judul</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User Penerima</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Deadline</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">File Jawaban</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nilai</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Komentar</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        $no = 1;
                        if ($result->num_rows > 0):
                            while($row = $result->fetch_assoc()): 
                        ?>
                        <tr class="hover:bg-gray-50 transition duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"><?php echo $no++; ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="font-medium truncate max-w-xs" title="<?php echo htmlspecialchars($row['judul']); ?>">
                                    <?php echo htmlspecialchars($row['judul']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['penerima_username']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($row['deadline'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if($row['file_jawaban']): ?>
                                    <a href="../../uploads/tugas_jawaban/<?php echo htmlspecialchars($row['file_jawaban']); ?>" target="_blank" 
                                       class="inline-flex items-center text-blue-600 hover:text-blue-800 transition duration-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        Lihat File
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">Tidak ada file</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($row['nilai'] !== null): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars($row['nilai']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Belum dinilai
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="truncate max-w-xs" title="<?php echo htmlspecialchars($row['komentar'] ?? '-'); ?>">
                                    <?php echo htmlspecialchars($row['komentar'] ?? '-'); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if($row['id_jawaban'] && $row['nilai'] === null): ?>
                                    <a href="tugas_user_review.php?id_tugas=<?php echo $row['id']; ?>" 
                                       class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition duration-200">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                        </svg>
                                        Beri Nilai
                                    </a>
                                <?php elseif ($row['id_jawaban'] && $row['nilai'] !== null): ?>
                                    <a href="tugas_user_review.php?id_tugas=<?php echo $row['id']; ?>" 
                                       class="inline-flex items-center px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded-md transition duration-200">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Lihat/Edit
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endwhile; 
                        else:
                        ?>
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-gray-500 text-lg">Tidak ada tugas yang sudah selesai atau diperiksa.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="lg:hidden">
                <?php 
                $result->data_seek(0); // Reset result pointer for mobile view
                $no = 1;
                if ($result->num_rows > 0):
                    while($row = $result->fetch_assoc()): 
                ?>
                <div class="border-b border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-semibold text-gray-500">#<?php echo $no++; ?></span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <?php echo htmlspecialchars($row['status']); ?>
                        </span>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($row['judul']); ?></h3>
                    
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p><span class="font-medium">User:</span> <?php echo htmlspecialchars($row['penerima_username']); ?></p>
                        <p><span class="font-medium">Deadline:</span> <?php echo date('d/m/Y', strtotime($row['deadline'])); ?></p>
                        <p><span class="font-medium">Nilai:</span> 
                            <?php if ($row['nilai'] !== null): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($row['nilai']); ?>
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Belum dinilai
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-2">
                        <?php if($row['file_jawaban']): ?>
                            <a href="../../uploads/tugas_jawaban/<?php echo htmlspecialchars($row['file_jawaban']); ?>" target="_blank" 
                               class="inline-flex items-center justify-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                Lihat File
                            </a>
                        <?php endif; ?>
                        
                        <?php if($row['id_jawaban'] && $row['nilai'] === null): ?>
                            <a href="tugas_user_review.php?id_tugas=<?php echo $row['id']; ?>" 
                               class="inline-flex items-center justify-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                                Beri Nilai
                            </a>
                        <?php elseif ($row['id_jawaban'] && $row['nilai'] !== null): ?>
                            <a href="tugas_user_review.php?id_tugas=<?php echo $row['id']; ?>" 
                               class="inline-flex items-center justify-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Lihat/Edit Nilai
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                    endwhile; 
                else:
                ?>
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500 text-lg">Tidak ada tugas yang sudah selesai atau diperiksa.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
?>