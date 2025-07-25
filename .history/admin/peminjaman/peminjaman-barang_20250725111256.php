<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil daftar peminjaman aktif saja (belum dikembalikan)
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
          WHERE pb.status = 'dipinjam'
          ORDER BY pb.tanggal_pinjam DESC";
$result = $conn->query($query);

// Ambil daftar anggota untuk form peminjaman
$query_anggota = "SELECT id, nama FROM anggota ORDER BY nama";
$anggota = $conn->query($query_anggota);

// Ambil daftar alat untuk form peminjaman
$query_alat = "SELECT id, nama_alat, jumlah FROM alat WHERE jumlah > 0 ORDER BY nama_alat";
$alat = $conn->query($query_alat);

// Proses form peminjaman dan update status pengembalian
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Jika admin klik tombol "Sudah dikembalikan"
    if (isset($_POST['kembalikan_id'])) {
        $id = $_POST['kembalikan_id'];
        $stmt = $conn->prepare("UPDATE peminjaman_barang SET status='dikembalikan', tanggal_kembali=CURDATE() WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: peminjaman-barang.php?status=success&message=Status peminjaman diupdate");
        exit;
    }

    // Proses tambah peminjaman
    $tipe_peminjam = $_POST['tipe_peminjam'];
    $id_alat = $_POST['id_alat'];
    $tanggal_pinjam = !empty($_POST['tanggal_pinjam']) ? $_POST['tanggal_pinjam'] : date('Y-m-d');
    $tanggal_kembali = !empty($_POST['tanggal_kembali']) ? $_POST['tanggal_kembali'] : null;
    $jumlah = $_POST['jumlah'];
    // Validasi stok
    $stmt = $conn->prepare("SELECT jumlah FROM alat WHERE id = ?");
    $stmt->bind_param("i", $id_alat);
    $stmt->execute();
    $result_stok = $stmt->get_result();
    $stok = $result_stok->fetch_assoc();
    if ($stok['jumlah'] >= $jumlah) {
        // Prepare statement berdasarkan tipe peminjam
        if ($tipe_peminjam === 'anggota') {
            if ($tanggal_kembali) {
                $id_anggota = $_POST['id_anggota'];
                $stmt = $conn->prepare("INSERT INTO peminjaman_barang (id_anggota, id_alat, tanggal_pinjam, tanggal_kembali, jumlah, status, tipe_peminjam) VALUES (?, ?, ?, ?, ?, 'dipinjam', 'anggota')");
                $stmt->bind_param("iissi", $id_anggota, $id_alat, $tanggal_pinjam, $tanggal_kembali, $jumlah);
            } else {
                $id_anggota = $_POST['id_anggota'];
                $stmt = $conn->prepare("INSERT INTO peminjaman_barang (id_anggota, id_alat, tanggal_pinjam, jumlah, status, tipe_peminjam) VALUES (?, ?, ?, ?, 'dipinjam', 'anggota')");
                $stmt->bind_param("iisi", $id_anggota, $id_alat, $tanggal_pinjam, $jumlah);
            }
        } else {
            $nama_peminjam = $_POST['nama_peminjam'];
            $kontak_peminjam = $_POST['kontak_peminjam'];
            if ($tanggal_kembali) {
                $stmt = $conn->prepare("INSERT INTO peminjaman_barang (id_alat, tanggal_pinjam, tanggal_kembali, jumlah, status, tipe_peminjam, nama_peminjam, kontak_peminjam) VALUES (?, ?, ?, ?, 'dipinjam', 'umum', ?, ?)");
                $stmt->bind_param("ississ", $id_alat, $tanggal_pinjam, $tanggal_kembali, $jumlah, $nama_peminjam, $kontak_peminjam);
            } else {
                $stmt = $conn->prepare("INSERT INTO peminjaman_barang (id_alat, tanggal_pinjam, jumlah, status, tipe_peminjam, nama_peminjam, kontak_peminjam) VALUES (?, ?, ?, 'dipinjam', 'umum', ?, ?)");
                $stmt->bind_param("isiss", $id_alat, $tanggal_pinjam, $jumlah, $nama_peminjam, $kontak_peminjam);
            }
        }
        if ($stmt->execute()) {
            // Update stok
            $stmt = $conn->prepare("UPDATE alat SET jumlah = jumlah - ? WHERE id = ?");
            $stmt->bind_param("ii", $jumlah, $id_alat);
            $stmt->execute();
            header("Location: peminjaman-barang.php?status=success&message=Peminjaman berhasil ditambahkan");
            exit;
        } else {
            header("Location: peminjaman-barang.php?status=error&message=Gagal menambahkan peminjaman");
            exit;
        }
    } else {
        header("Location: peminjaman-barang.php?status=error&message=Stok tidak mencukupi");
        exit;
    }
}
include '../header_beckend.php';
include '../header.php';
?>

<body class=" from-blue-50 to-white min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header Section -->
        <div class="bg-white rounded-xl shadow-lg border border-blue-100 mb-8">
            <div class="px-8 py-6 border-b border-blue-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-blue-primary rounded-lg p-3">
                            <i class="fas fa-handshake text-black text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Peminjaman Barang</h1>
                            <p class="text-gray-600 text-sm">Kelola peminjaman alat dan barang</p>
                        </div>
                    </div>
                    <a href="riwayat-peminjaman-barang.php" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg transition-all duration-200 flex items-center space-x-2 shadow-md hover:shadow-lg">
                        <i class="fas fa-history"></i>
                        <span>Lihat Riwayat Peminjaman</span>
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
        <div class="bg-white rounded-xl shadow-lg border border-blue-100 mb-8">
            <div class="px-8 py-6 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-white">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-plus-circle text-blue-primary text-lg"></i>
                    <h2 class="text-xl font-semibold text-gray-800">Form Peminjaman Baru</h2>
                </div>
            </div>
            
            <div class="p-8">
                <form method="POST" class="space-y-6">
                    <!-- Tipe Peminjam -->
                    <div class="space-y-2">
                        <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user-tag text-blue-primary mr-2"></i>
                            Tipe Peminjam
                        </label>
                        <div class="relative">
                            <select name="tipe_peminjam" id="tipe_peminjam" onchange="togglePeminjam()" required
                                    class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-primary focus:border-blue-primary transition-all duration-200 appearance-none bg-white">
                                <option value="umum">Umum</option>
                                <option value="anggota">Anggota</option>
                            </select>
                            <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                                <i class="fas fa-users text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Form Umum -->
                    <div id="form_umum" class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-user text-blue-primary mr-2"></i>
                                    Nama Peminjam
                                </label>
                                <div class="relative">
                                    <input type="text" name="nama_peminjam" id="nama_peminjam" required
                                           class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-primary focus:border-blue-primary transition-all duration-200">
                                    <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                                        <i class="fas fa-user-circle text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-phone text-blue-primary mr-2"></i>
                                    Kontak Peminjam
                                </label>
                                <div class="relative">
                                    <input type="text" name="kontak_peminjam" id="kontak_peminjam" required
                                           class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-primary focus:border-blue-primary transition-all duration-200">
                                    <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                                        <i class="fas fa-phone text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Anggota -->
                    <div id="form_anggota" style="display:none" class="space-y-2">
                        <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-id-badge text-blue-primary mr-2"></i>
                            Pilih Anggota
                        </label>
                        <div class="relative">
                            <select name="id_anggota" id="id_anggota"
                                    class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-primary focus:border-blue-primary transition-all duration-200 appearance-none bg-white">
                                <option value="">Pilih Anggota</option>
                                <?php while($row = $anggota->fetch_assoc()): ?>
                                    <option value="<?php echo $row['id']; ?>">
                                        <?php echo htmlspecialchars($row['nama']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                                <i class="fas fa-id-card text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Alat -->
                    <div class="space-y-2">
                        <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-tools text-blue-primary mr-2"></i>
                            Alat
                        </label>
                        <div class="relative">
                            <select name="id_alat" required
                                    class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-primary focus:border-blue-primary transition-all duration-200 appearance-none bg-white">
                                <option value="">Pilih Alat</option>
                                <?php while($row = $alat->fetch_assoc()): ?>
                                    <option value="<?php echo $row['id']; ?>">
                                        <?php echo htmlspecialchars($row['nama_alat']); ?> (Stok: <?php echo $row['jumlah']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                                <i class="fas fa-wrench text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tanggal -->
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar text-blue-primary mr-2"></i>
                                Tanggal Pinjam
                            </label>
                            <div class="relative">
                                <input type="date" name="tanggal_pinjam" value="<?php echo date('Y-m-d'); ?>" required
                                       class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-primary focus:border-blue-primary transition-all duration-200">          
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-check text-blue-primary mr-2"></i>
                                Tanggal Kembali 
                            </label>
                            <div class="relative">
                                <input type="date" name="tanggal_kembali"
                                       class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-primary focus:border-blue-primary transition-all duration-200">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Jumlah -->
                    <div class="space-y-2">
                        <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-hashtag text-blue-primary mr-2"></i>
                            Jumlah
                        </label>
                        <div class="relative">
                            <input type="number" name="jumlah" min="1" required
                                   class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-primary focus:border-blue-primary transition-all duration-200">
                            <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                                <i class="fas fa-calculator text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="pt-6 border-t border-gray-200 flex items-center">
                        <button type="submit" 
                                class="w-rounded px-4 py-2 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-plus"></i>
                            <span>Tambah Peminjaman</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Table Section -->
        <div class="bg-white rounded-xl shadow-lg border border-blue-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-white">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-list text-blue-primary text-lg"></i>
                    <h2 class="text-xl font-semibold text-gray-800">Daftar Peminjaman Aktif</h2>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-blue-primary text-black">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Nama Peminjam</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Kontak</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Nama Alat</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Tanggal Pinjam</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Tanggal Kembali</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Status</th>
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
                                    <?php 
                                    if (!empty($row['tanggal_pinjam']) && $row['tanggal_pinjam'] !== '0000-00-00') {
                                        echo date('d/m/Y', strtotime($row['tanggal_pinjam']));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-check text-green-500 mr-2"></i>
                                    <?php 
                                    if (!empty($row['tanggal_kembali']) && $row['tanggal_kembali'] !== '0000-00-00') {
                                        echo date('d/m/Y', strtotime($row['tanggal_kembali']));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                                    <?php echo $row['jumlah']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($row['status'] === 'dipinjam'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="kembalikan_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" onclick="return confirm('Tandai sebagai sudah dikembalikan?')"
                                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-full text-xs font-medium transition-all duration-200">
                                            Belum
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium">
                                        Sudah
                                    </span>
                                <?php endif; ?>
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
                            <td colspan="10" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-lg font-medium">Belum ada peminjaman aktif</p>
                                    <p class="text-gray-400 text-sm">Tambah peminjaman baru menggunakan form di atas</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Initialize form on page load
        document.addEventListener('DOMContentLoaded', function() {
            togglePeminjam();
        });
    </script>
</body>
</html>

<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
?>