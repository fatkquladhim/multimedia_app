<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil daftar penyewaan aktif
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
          WHERE sb.status = 'disewa'
          ORDER BY sb.tanggal_sewa DESC";
$result = $conn->query($query);

// Ambil daftar anggota untuk form penyewaan
$query_anggota = "SELECT id, nama FROM anggota ORDER BY nama";
$anggota = $conn->query($query_anggota);

// Ambil daftar alat untuk form penyewaan
$query_alat = "SELECT id, nama_alat, jumlah FROM alat WHERE jumlah > 0 ORDER BY nama_alat";
$alat = $conn->query($query_alat);

// Proses form penyewaan dan update status pengembalian
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Jika admin klik tombol "Sudah dikembalikan"
    if (isset($_POST['kembalikan_id'])) {
        $id = $_POST['kembalikan_id'];
        $stmt = $conn->prepare("UPDATE penyewaan_barang SET status='dikembalikan', tanggal_kembali=CURDATE() WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: penyewaan-barang.php?status=success&message=Status penyewaan diupdate");
        exit;
    }
    $tipe_penyewa = $_POST['tipe_penyewa'];
    $id_alat = $_POST['id_alat'];
    $tanggal_sewa = !empty($_POST['tanggal_sewa']) ? $_POST['tanggal_sewa'] : date('Y-m-d');
    $jumlah = $_POST['jumlah'];
    $biaya = $_POST['biaya'];
    $tanggal_kembali = !empty($_POST['tanggal_kembali']) ? $_POST['tanggal_kembali'] : null;
    // Validasi stok
    $stmt = $conn->prepare("SELECT jumlah FROM alat WHERE id = ?");
    $stmt->bind_param("i", $id_alat);
    $stmt->execute();
    $result_stok = $stmt->get_result();
    $stok = $result_stok->fetch_assoc();
    if ($stok['jumlah'] >= $jumlah) {
        // Prepare statement berdasarkan tipe penyewa
        if ($tipe_penyewa === 'anggota') {
            $id_anggota = $_POST['id_anggota'];
            if ($tanggal_kembali) {
                $stmt = $conn->prepare("INSERT INTO penyewaan_barang (id_anggota, id_alat, tanggal_sewa, tanggal_kembali, jumlah, biaya, status, tipe_penyewa) VALUES (?, ?, ?, ?, ?, ?, 'disewa', 'anggota')");
                $stmt->bind_param("iissid", $id_anggota, $id_alat, $tanggal_sewa, $tanggal_kembali, $jumlah, $biaya);
            } else {
                $stmt = $conn->prepare("INSERT INTO penyewaan_barang (id_anggota, id_alat, tanggal_sewa, jumlah, biaya, status, tipe_penyewa) VALUES (?, ?, ?, ?, ?, 'disewa', 'anggota')");
                $stmt->bind_param("iisid", $id_anggota, $id_alat, $tanggal_sewa, $jumlah, $biaya);
            }
        } else {
            $nama_penyewa = $_POST['nama_penyewa'];
            $kontak_penyewa = $_POST['kontak_penyewa'];
            if ($tanggal_kembali) {
                $stmt = $conn->prepare("INSERT INTO penyewaan_barang (id_alat, tanggal_sewa, tanggal_kembali, jumlah, biaya, status, tipe_penyewa, nama_penyewa, kontak_penyewa) VALUES (?, ?, ?, ?, ?, 'disewa', 'umum', ?, ?)");
                $stmt->bind_param("issidss", $id_alat, $tanggal_sewa, $tanggal_kembali, $jumlah, $biaya, $nama_penyewa, $kontak_penyewa);
            } else {
                $stmt = $conn->prepare("INSERT INTO penyewaan_barang (id_alat, tanggal_sewa, jumlah, biaya, status, tipe_penyewa, nama_penyewa, kontak_penyewa) VALUES (?, ?, ?, ?, 'disewa', 'umum', ?, ?)");
                $stmt->bind_param("isidss", $id_alat, $tanggal_sewa, $jumlah, $biaya, $nama_penyewa, $kontak_penyewa);
            }
        }
        if ($stmt->execute()) {
            // Update stok
            $stmt = $conn->prepare("UPDATE alat SET jumlah = jumlah - ? WHERE id = ?");
            $stmt->bind_param("ii", $jumlah, $id_alat);
            $stmt->execute();
            header("Location: penyewaan-barang.php?status=success&message=Penyewaan berhasil ditambahkan");
            exit;
        } else {
            header("Location: penyewaan-barang.php?status=error&message=Gagal menambahkan penyewaan");
            exit;
        }
    } else {
        header("Location: penyewaan-barang.php?status=error&message=Stok tidak mencukupi");
        exit;
    }
}
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
                        <i class="fas fa-handshake text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Penyewaan Barang</h1>
                        <p class="text-gray-600">Kelola penyewaan barang dan alat</p>
                    </div>
                </div>
                <a href="riwayat-penyewaan-barang.php" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-2 rounded-xl transition-colors duration-200 flex items-center space-x-2">
                    <i class="fas fa-history"></i>
                    <span>Lihat Riwayat</span>
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
        </div>
        
        <!-- Form Penyewaan Section -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 border border-blue-100">
            <div class="flex items-center space-x-3 mb-6">
                <div class="bg-blue-100 p-2 rounded-lg">
                    <i class="fas fa-plus text-blue-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Form Penyewaan Baru</h2>
            </div>
            
            <form method="POST" class="space-y-6">
                <!-- Tipe Penyewa -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>Tanggal Kembali
                        </label>
                        <input type="date" name="tanggal_kembali" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all duration-200">
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fas fa-plus mr-2"></i>Tambah Penyewaan
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Daftar Penyewaan Aktif -->
        <div class="bg-white rounded-2xl shadow-xl border border-blue-100 overflow-hidden">
            <div class="p-8 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="bg-green-100 p-2 rounded-lg">
                        <i class="fas fa-list text-green-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Daftar Penyewaan Aktif</h2>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
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
                            <th class="px-6 py-4 text-left text-xs font-semibold text-blue-800 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-blue-800 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php 
                        $no = 1;
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['nama_alat']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?php 
                                if (!empty($row['tanggal_sewa']) && $row['tanggal_sewa'] !== '0000-00-00') {
                                    echo date('d/m/Y', strtotime($row['tanggal_sewa']));
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?php 
                                if (!empty($row['tanggal_kembali']) && $row['tanggal_kembali'] !== '0000-00-00') {
                                    echo date('d/m/Y', strtotime($row['tanggal_kembali']));
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo $row['jumlah']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                Rp <?php echo number_format($row['biaya'], 0, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($row['status'] === 'disewa'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="kembalikan_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" onclick="return confirm('Tandai sebagai sudah dikembalikan?')" 
                                                class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 hover:bg-red-200 transition-colors duration-200">
                                            <i class="fas fa-clock mr-1"></i>Belum
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Sudah
                                    </span>
                                <?php endif; ?>
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
        </div>
    </div>

    <script>
        function togglePenyewa() {
            const tipePenyewa = document.getElementById('tipe_penyewa').value;
            const formUmum = document.getElementById('form_umum');
            const formAnggota = document.getElementById('form_anggota');
            const namaUmum = document.getElementById('nama_penyewa');
            const kontakUmum = document.getElementById('kontak_penyewa');
            const idAnggota = document.getElementById('id_anggota');
            
            if (tipePenyewa === 'anggota') {
                formUmum.style.display = 'none';
                formAnggota.style.display = 'grid';
                namaUmum.required = false;
                kontakUmum.required = false;
                idAnggota.required = true;
            } else {
                formUmum.style.display = 'grid';
                formAnggota.style.display = 'none';
                namaUmum.required = true;
                kontakUmum.required = true;
                idAnggota.required = false;
            }
        }
    </script>
</body>
</html>

<?php
// Sertakan footer
include '../footer.php';
$conn->close();
?>
                            