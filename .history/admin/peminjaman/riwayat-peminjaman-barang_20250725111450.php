<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil riwayat peminjaman
$query = "SELECT pb.*, a.nama as nama_anggota, al.nama_alat as nama_alat,
          CASE 
              WHEN pb.tipe_peminjam = 'umum' THEN pb.nama_peminjam 
              ELSE a.nama 
          END as nama_peminjam,
          CASE 
              WHEN pb.tipe_peminjam = 'umum' THEN pb.kontak_peminjam 
              ELSE '-'
          END as kontak
          FROM peminjaman_barang pb 
          LEFT JOIN anggota a ON pb.id_anggota = a.id 
          LEFT JOIN alat al ON pb.id_alat = al.id 
          WHERE pb.status = 'dikembalikan'
          ORDER BY pb.tanggal_kembali DESC";
$result = $conn->query($query);
include '../header_beckend.php';
include '../header.php';
?>

<body class="bg-gradient-to-br from-blue-50 to-white min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header Section -->
        <div class="bg-white rounded-xl shadow-lg border border-blue-100 mb-8">
            <div class="px-8 py-6 border-b border-blue-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-blue-primary rounded-lg p-3">
                            <i class="fas fa-history text-white text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Riwayat Peminjaman Barang</h1>
                            <p class="text-gray-600 text-sm">Kelola riwayat peminjaman barang yang sudah dikembalikan</p>
                        </div>
                    </div>
                    <a href="peminjaman-barang.php" class="bg-blue-primary hover:bg-blue-dark text-gray px-6 py-3 rounded-lg transition-all duration-200 flex items-center space-x-2 shadow-md hover:shadow-lg">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali ke Daftar Peminjaman</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="mb-6">
                <?php if ($_GET['status'] === 'success'): ?>
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-400 mr-3"></i>
                            <p class="text-green-800 font-medium"><?php echo htmlspecialchars($_GET['message']); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-sm">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                            <p class="text-red-800 font-medium"><?php echo htmlspecialchars($_GET['message']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Table Section -->
        <div class="bg-white rounded-xl shadow-lg border border-blue-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-white">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-table text-blue-primary text-lg"></i>
                    <h2 class="text-xl font-semibold text-gray-800">Data Riwayat Peminjaman</h2>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-blue-primary text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Nama Peminjam</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Kontak</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Nama Alat</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Tanggal Pinjam</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Tanggal Kembali</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php 
                        $no = 1;
                        while($row = $result->fetch_assoc()): 
                        ?>
                        <tr class="hover:bg-blue-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full">
                                    <span class="text-sm font-medium text-blue-primary"><?php echo $no++; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_peminjam']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo htmlspecialchars($row['kontak']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full <?php echo $row['tipe_peminjam'] === 'anggota' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo htmlspecialchars($row['tipe_peminjam']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-tools text-green-600 text-xs"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_alat']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center">
                                    <i class="far fa-calendar text-blue-400 mr-2"></i>
                                    <?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-check text-green-500 mr-2"></i>
                                    <?php echo $row['tanggal_kembali'] ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                                    <?php echo $row['jumlah']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-2">
                                    <a href="edit-barang-peminjaman.php?id=<?php echo $row['id']; ?>" 
                                       class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg transition-all duration-200 flex items-center space-x-1 shadow-sm hover:shadow-md">
                                        <i class="fas fa-edit text-xs"></i>
                                        <span>Edit</span>
                                    </a>
                                    <a href="hapus-peminjaman.php?id=<?php echo $row['id']; ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus peminjaman ini?')"
                                       class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg transition-all duration-200 flex items-center space-x-1 shadow-sm hover:shadow-md">
                                        <i class="fas fa-trash text-xs"></i>
                                        <span>Hapus</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if ($result->num_rows === 0): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-lg font-medium">Belum ada riwayat peminjaman</p>
                                    <p class="text-gray-400 text-sm">Data riwayat peminjaman akan muncul di sini</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
?>