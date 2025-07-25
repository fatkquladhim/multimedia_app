<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil data peminjaman yang akan diedit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT pb.*, a.nama as nama_anggota, al.nama_alat as nama_alat 
                           FROM peminjaman_barang pb 
                           LEFT JOIN anggota a ON pb.id_anggota = a.id 
                           LEFT JOIN alat al ON pb.id_alat = al.id 
                           WHERE pb.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $peminjaman = $result->fetch_assoc();

    if (!$peminjaman) {
        header("Location: peminjaman-barang.php?status=error&message=Peminjaman tidak ditemukan");
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
            // Update peminjaman
            $stmt = $conn->prepare("UPDATE peminjaman_barang SET id_alat = ?, jumlah = ?, tanggal_kembali = ? WHERE id = ?");
            $stmt->bind_param("iisi", $id_alat_baru, $jumlah_baru, $tanggal_kembali, $id);
            $stmt->execute();
            
            // Kurangi stok baru
            $stmt = $conn->prepare("UPDATE alat SET jumlah = jumlah - ? WHERE id = ?");
            $stmt->bind_param("ii", $jumlah_baru, $id_alat_baru);
            $stmt->execute();
            
            $conn->commit();
            header("Location: peminjaman-barang.php?status=success&message=Peminjaman berhasil diupdate");
            exit;
        } else {
            throw new Exception("Stok tidak mencukupi");
        }
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: edit-barang-peminjaman.php?id=$id&status=error&message=" . $e->getMessage());
        exit;
    }
}
include '../header_beckend.php';
include '../header.php';
?>

<body class="bg-gradient-to-br from-blue-50 to-white min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header Section -->
        <div class="bg-white rounded-xl shadow-lg border border-blue-100 mb-8">
            <div class="px-8 py-6 border-b border-blue-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-blue-primary rounded-lg p-3">
                            <i class="fas fa-edit text-gray text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Edit Peminjaman Barang</h1>
                            <p class="text-gray-600 text-sm">Ubah data peminjaman barang</p>
                        </div>
                    </div>
                    <a href="peminjaman-barang.php" class="bg-green-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition-all duration-200 flex items-center space-x-2 shadow-md hover:shadow-lg">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
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
        
        <!-- Form Section -->
        <div class="bg-white rounded-xl shadow-lg border border-blue-100">
            <div class="px-8 py-6 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-white">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-form text-blue-primary text-lg"></i>
                    <h2 class="text-xl font-semibold text-gray-800">Form Edit Peminjaman</h2>
                </div>
            </div>
            
            <div class="p-8">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="id" value="<?php echo $peminjaman['id']; ?>">
                    <input type="hidden" name="id_alat_lama" value="<?php echo $peminjaman['id_alat']; ?>">
                    <input type="hidden" name="jumlah_lama" value="<?php echo $peminjaman['jumlah']; ?>">
                    
                    <!-- Peminjam Field -->
                    <div class="space-y-2">
                        <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user text-blue-primary mr-2"></i>
                            Peminjam
                        </label>
                        <div class="relative">
                            <?php if ($peminjaman['tipe_peminjam'] === 'umum'): ?>
                                <input type="text" 
                                       value="<?php echo htmlspecialchars($peminjaman['nama_peminjam']); ?>" 
                                       disabled
                                       class="w-full px-4 py-3 pl-12 bg-gray-100 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed focus:outline-none">
                            <?php else: ?>
                                <input type="text" 
                                       value="<?php echo htmlspecialchars($peminjaman['nama_anggota']); ?>" 
                                       disabled
                                       class="w-full px-4 py-3 pl-12 bg-gray-100 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed focus:outline-none">
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Alat Field -->
                    <div class="space-y-2">
                        <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-tools text-blue-primary mr-2"></i>
                            Alat
                        </label>
                        <div class="relative">
                            <select name="id_alat" required
                                    class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-primary focus:border-blue-primary transition-all duration-200 appearance-none bg-white">
                                <?php 
                                while($row = $alat->fetch_assoc()): 
                                    $selected = ($row['id'] === $peminjaman['id_alat']) ? 'selected' : '';
                                    $stok_tersedia = $row['jumlah'] + ($row['id'] === $peminjaman['id_alat'] ? $peminjaman['jumlah'] : 0);
                                ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($row['nama_alat']); ?> 
                                        (Stok: <?php echo $stok_tersedia; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                                <i class="fas fa-wrench text-gray-400"></i>
                            </div>
                           
                        </div>
                    </div>
                    
                    <!-- Jumlah Field -->
                    <div class="space-y-2">
                        <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-hashtag text-blue-primary mr-2"></i>
                            Jumlah
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   name="jumlah" 
                                   value="<?php echo $peminjaman['jumlah']; ?>" 
                                   min="1" 
                                   required
                                   class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-primary focus:border-blue-primary transition-all duration-200">
                        </div>
                    </div>
                    
                    <!-- Tanggal Kembali Field -->
                    <div class="space-y-2">
                        <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar-check text-blue-primary mr-2"></i>
                            Tanggal Kembali
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   name="tanggal_kembali" 
                                   value="<?php echo $peminjaman['tanggal_kembali']; ?>"
                                   class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-primary focus:border-blue-primary transition-all duration-200">
                        </div>
                        <p class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            Kosongkan jika belum dikembalikan
                        </p>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="peminjaman-barang.php" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition-all duration-200 flex items-center space-x-2 shadow-md hover:shadow-lg">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </a>
                        
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-dark text-white px-8 py-3 rounded-lg transition-all duration-200 flex items-center space-x-2 shadow-md hover:shadow-lg">
                            <i class="fas fa-save"></i>
                            <span>Update Peminjaman</span>
                        </button>
                    </div>
                </form>
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