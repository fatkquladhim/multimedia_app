<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}
require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Sertakan header
include '../header_beckend.php';
include '../header.php';
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 border border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                        Daftar Anggota
                    </h1>
                    <p class="text-gray-600 text-lg">Kelola data anggota dengan mudah dan efisien</p>
                </div>
                <a href="anggota-entry.php" 
                   class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Anggota
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php
        if (isset($_GET['status']) && isset($_GET['message'])) {
            $alertClass = $_GET['status'] === 'success' 
                ? 'bg-green-50 border-green-200 text-green-800' 
                : 'bg-red-50 border-red-200 text-red-800';
            $iconPath = $_GET['status'] === 'success' 
                ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                : 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z';
            
            echo "<div class='mb-6 p-4 rounded-xl border {$alertClass} flex items-center'>
                    <svg class='w-6 h-6 mr-3' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='{$iconPath}'></path>
                    </svg>
                    <span class='font-medium'>" . htmlspecialchars($_GET['message']) . "</span>
                  </div>";
        }
        ?>

        <!-- Members Table -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    Data Anggota
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-blue-50 to-purple-50">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Alamat</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">No HP</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Foto</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        $result = $conn->query('SELECT * FROM anggota ORDER BY id DESC');
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 transition-all duration-200">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600 max-w-xs truncate" title="<?php echo htmlspecialchars($row['alamat']); ?>">
                                    <?php echo htmlspecialchars($row['alamat']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600"><?php echo htmlspecialchars($row['email']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600"><?php echo htmlspecialchars($row['no_hp']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($row['foto']) { ?>
                                    <div class="flex-shrink-0 h-12 w-12">
                                        <img class="h-12 w-12 rounded-full object-cover border-2 border-gray-200 shadow-sm" 
                                             src="../../uploads/<?php echo htmlspecialchars($row['foto']); ?>" 
                                             alt="Foto Anggota">
                                    </div>
                                <?php } else { ?>
                                    <div class="flex-shrink-0 h-12 w-12 bg-gray-200 rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                <?php } ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-3">
                                    <a href="anggota-edit.php?edit=<?php echo $row['id']; ?>" 
                                       class="inline-flex items-center px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors duration-200 text-sm font-medium">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit
                                    </a>
                                    <a href="anggota-hapus.php?id=<?php echo $row['id']; ?>" 
                                       onclick="return confirm('Yakin hapus anggota?')"
                                       class="inline-flex items-center px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors duration-200 text-sm font-medium">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                        
                        <?php if (mysqli_num_rows($result) == 0) { ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                    <p class="text-gray-500 text-lg font-medium">Belum ada data anggota</p>
                                    <p class="text-gray-400 mt-1">Klik tombol "Tambah Anggota" untuk menambah data pertama</p>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Sertakan footer
include '../footer.php';
$conn->close();
?>