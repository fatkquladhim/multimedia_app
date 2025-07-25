<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil data penyewaan yang akan diedit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT pb.*, a.nama as nama_anggota, al.nama_alat as nama_alat 
                           FROM penyewaan_barang pb 
                           LEFT JOIN anggota a ON pb.id_anggota = a.id 
                           LEFT JOIN alat al ON pb.id_alat = al.id 
                           WHERE pb.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $penyewaan = $result->fetch_assoc();

    if (!$penyewaan) {
        header("Location: penyewaan-barang.php?status=error&message=Penyewaan tidak ditemukan");
        exit;
    }
}

// Ambil daftar alat untuk form edit
$query_alat = "SELECT id, nama_alat, jumlah FROM alat ORDER BY nama_alat";
$alat = $conn->query($query_alat);

// Proses form edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $id_alat_lama = $_POST['id_alat_lama'];
    $jumlah_lama = $_POST['jumlah_lama'];
    $id_alat_baru = $_POST['id_alat'];
    $jumlah_baru = $_POST['jumlah'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    
    // Mulai transaction
    $conn->begin_transaction();
    
    try {
        // Kembalikan stok lama
        $stmt = $conn->prepare("UPDATE alat SET jumlah = jumlah + ? WHERE id = ?");
        $stmt->bind_param("ii", $jumlah_lama, $id_alat_lama);
        $stmt->execute();
        
        // Validasi stok baru
        $stmt = $conn->prepare("SELECT jumlah FROM alat WHERE id = ?");
        $stmt->bind_param("i", $id_alat_baru);
        $stmt->execute();
        $result_stok = $stmt->get_result();
        $stok = $result_stok->fetch_assoc();
        
        if ($stok['jumlah'] >= $jumlah_baru) {
            // Update penyewaan
            $stmt = $conn->prepare("UPDATE penyewaan_barang SET id_alat = ?, jumlah = ?, tanggal_kembali = ? WHERE id = ?");
            $stmt->bind_param("iisi", $id_alat_baru, $jumlah_baru, $tanggal_kembali, $id);
            $stmt->execute();
            
            // Kurangi stok baru
            $stmt = $conn->prepare("UPDATE alat SET jumlah = jumlah - ? WHERE id = ?");
            $stmt->bind_param("ii", $jumlah_baru, $id_alat_baru);
            $stmt->execute();
            
            $conn->commit();
            header("Location: penyewaan-barang.php?status=success&message=Penyewaan berhasil diupdate");
            exit;
        } else {
            throw new Exception("Stok tidak mencukupi");
        }
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: edit-barang-penyewaan.php?id=$id&status=error&message=" . $e->getMessage());
        exit;
    }
}
include '../header_beckend.php';
include '../header.php';
?>
<body class="bg-gradient-to-br from-blue-50 to-white min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 border border-blue-100">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-600 p-3 rounded-xl">
                        <i class="fas fa-edit text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Edit Penyewaan Barang</h1>
                        <p class="text-gray-600">Ubah data penyewaan barang</p>
                    </div>
                </div>
                <a href="penyewaan-barang.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl transition-colors duration-200 flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
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
        
        <!-- Form Section -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-blue-100">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="id" value="<?php echo $penyewaan['id']; ?>">
                <input type="hidden" name="id_alat_lama" value="<?php echo $penyewaan['id_alat']; ?>">
                <input type="hidden" name="jumlah_lama" value="<?php echo $penyewaan['jumlah']; ?>">
                
                <!-- Penyewa Info -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-user text-blue-600 mr-2"></i>Penyewa
                        </label>
                        <div class="relative">
                            <?php if ($penyewaan['tipe_penyewa'] === 'umum'): ?>
                                <input type="text" value="<?php echo htmlspecialchars($penyewaan['nama_penyewa']); ?>" 
                                       disabled class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 focus:outline-none">
                            <?php else: ?>
                                <input type="text" value="<?php echo htmlspecialchars($penyewaan['nama_anggota']); ?>" 
                                       disabled class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 focus:outline-none">
                            <?php endif; ?>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                    <?php echo $penyewaan['tipe_penyewa']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Alat Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-tools text-blue-600 mr-2"></i>Alat
                        </label>
                        <select name="id_alat" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all duration-200">
                            <?php 
                            while($row = $alat->fetch_assoc()): 
                                $selected = ($row['id'] === $penyewaan['id_alat']) ? 'selected' : '';
                                $stok_tersedia = $row['jumlah'] + ($row['id'] === $penyewaan['id_alat'] ? $penyewaan['jumlah'] : 0);
                            ?>
                                <option value="<?php echo $row['id']; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($row['nama_alat']); ?> 
                                    (Stok: <?php echo $stok_tersedia; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Jumlah dan Tanggal -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-hashtag text-blue-600 mr-2"></i>Jumlah
                        </label>
                        <input type="number" name="jumlah" value="<?php echo $penyewaan['jumlah']; ?>" min="1" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all duration-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>Tanggal Kembali
                        </label>
                        <input type="date" name="tanggal_kembali" value="<?php echo $penyewaan['tanggal_kembali']; ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all duration-200">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika belum dikembalikan</p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6 border-t border-gray-200">
                    <a href="penyewaan-barang.php" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors duration-200 text-center font-medium">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fas fa-save mr-2"></i>Update Penyewaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php
// Sertakan footer
include '../footer.php';
$conn->close();
?>