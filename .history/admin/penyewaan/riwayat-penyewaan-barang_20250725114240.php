<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil riwayat penyewaan
$query = "SELECT sb.*, a.nama as nama_anggota, al.nama_alat as nama_alat,
          CASE 
              WHEN sb.tipe_penyewa = 'umum' THEN sb.nama_penyewa 
              ELSE a.nama 
          END as nama_penyewa,
          CASE 
              WHEN sb.tipe_penyewa = 'umum' THEN sb.kontak_penyewa 
              ELSE '-'
          END as kontak
          FROM penyewaan_barang sb 
          LEFT JOIN anggota a ON sb.id_anggota = a.id 
          LEFT JOIN alat al ON sb.id_alat = al.id 
          WHERE sb.status = 'dikembalikan'
          ORDER BY sb.tanggal_kembali DESC";
$result = $conn->query($query);
include '../header_beckend.php';
include '../header.php';
?>
<body class="bg-gradient-to-br from-blue-50 to-white min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 border border-blue-100">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-600 p-3 rounded-xl">
                        <i class="fas fa-history text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Riwayat Penyewaan Barang</h1>
                        <p class="text-gray-600">Daftar penyewaan yang telah dikembalikan</p>
                    </div>
                </div>
                <a href="penyewaan-barang.php" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-2 rounded-xl transition-colors duration-200 flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke Penyewaan</span>
                </a>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
                <div class="mb-6 p-4 rounded-xl <?php echo $_GET['status'] === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'; ?>">
                    <div class="flex items-center space-x-2">
                        <i class="fas <?php echo $_GET['status'] === 'success' ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-red-500'; ?>"></i>
                        <span class="font-medium"><?php echo htmlspecialchars($_GET['message']); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm">Total Dikembalikan</p>
                            <p class="text-2xl font-bold"><?php echo $result->num_rows; ?></p>
                        </div>
                        <div class="bg-green-400 p-3 rounded-lg">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm">Bulan Ini</p>
                            <p class="text-2xl font-bold">
                                <?php 
                                $current_month = date('Y-m');
                                $monthly_count = 0;
                                $result_copy = $conn->query($query);
                                while($row = $result_copy->fetch_assoc()) {
                                    if (strpos($row['tanggal_kembali'], $current_month) === 0) {
                                        $monthly_count++;
                                    }
                                }
                                echo $monthly_count;
                                ?>
                            </p>
                        </div>
                        <div class="bg-blue-400 p-3 rounded-lg">
                            <i class="fas fa-history text-white text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm">Total Pendapatan</p>
                            <p class="text-2xl font-bold">
                                <?php 
                                $total_revenue = 0;
                                $result_revenue = $conn->query($query);
                                while($row = $result_revenue->fetch_assoc()) {
                                    $total_revenue += $row['biaya'];
                                }
                                echo 'Rp ' . number_format($total_revenue, 0, ',', '.');
                                ?>
                            </p>
                        </div>
                        <div class="bg-purple-400 p-3 rounded-lg">
                            <i class="fas fa-money-bill-wave text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Table Section -->
        <div class="bg-white rounded-2xl shadow-xl border border-blue-100 overflow-hidden">
            <div class="p-8 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-gray-100 p-2 rounded-lg">
                            <i class="fas fa-table text-gray-600"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Data Riwayat Lengkap</h2>
                    </div>
                    
                    <!-- Search and Filter -->
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Cari penyewa..." 
                                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full" id="riwayatTable">
                    <thead>
                        <tr class="bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                            <th class="px-6 py-4 text-left text-xs font-semibold text-blue-800 uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-blue-800 uppercase tracking-wider">Nama Penyewa</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-blue-800 uppercase tracking-wider">Kontak</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-blue-800 uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-blue-800 uppercase tracking-wider">Nama Alat</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-blue-800 uppercase tracking-wider">Tanggal Sewa</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-blue-800 uppercase tracking-wider">Tanggal Kembali</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-blue-800 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-blue-800 uppercase tracking-wider">Biaya</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-blue-800 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="tableBody">
                        <?php 
                        $no = 1;
                        $result->data_seek(0); // Reset result pointer
                        while($row = $result->fetch_assoc()): 
                        ?>
                        <tr class="hover:bg-blue-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $no++; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="bg-blue-100 p-2 rounded-full mr-3">
                                        <i class="fas fa-user text-blue-600 text-xs"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_penyewa']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($row['kontak']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $row['tipe_penyewa'] === 'anggota' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo htmlspecialchars($row['tipe_penyewa']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="bg-gray-100 p-2 rounded-full mr-3">
                                        <i class="fas fa-tools text-gray-600 text-xs"></i>
                                    </div>
                                    <span class="text-sm text-gray-900"><?php echo htmlspecialchars($row['nama_alat']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar text-blue-500 mr-2"></i>
                                    <?php echo date('d/m/Y', strtotime($row['tanggal_sewa'])); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-check text-green-500 mr-2"></i>
                                    <?php echo $row['tanggal_kembali'] ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo $row['jumlah']; ?> unit
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-money-bill text-green-500 mr-2"></i>
                                    <span class="text-sm font-medium text-green-600">
                                        Rp <?php echo number_format($row['biaya'], 0, ',', '.'); ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="edit-barang-penyewaan.php?id=<?php echo $row['id']; ?>" 
                                       class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1 rounded-lg transition-colors duration-200 text-xs">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </a>
                                    <a href="hapus-penyewaan.php?id=<?php echo $row['id']; ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus penyewaan ini?')"
                                       class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1 rounded-lg transition-colors duration-200 text-xs">
                                        <i class="fas fa-trash mr-1"></i>Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Empty State -->
            <?php if ($result->num_rows === 0): ?>
            <div class="text-center py-16">
                <div class="bg-gray-100 p-6 rounded-full w-24 h-24 mx-auto mb-6 flex items-center justify-center">
                    <i class="fas fa-inbox text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Riwayat</h3>
                <p class="text-gray-500">Belum ada penyewaan yang dikembalikan</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php
// Sertakan footer
include '../footer.php';
$conn->close();
?>