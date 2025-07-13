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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Penyewaan Barang</title>
    <style>
        .container { padding: 20px; }
        .form-group { margin-bottom: 10px; }
        .alert { padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border: 1px solid #ddd; }
        th { background-color: #f5f5f5; }
    </style>
    <script>
        function togglePenyewa() {
            var tipe = document.getElementById('tipe_penyewa').value;
            if (tipe === 'umum') {
                document.getElementById('form_umum').style.display = 'block';
                document.getElementById('form_anggota').style.display = 'none';
                document.getElementById('nama_penyewa').required = true;
                document.getElementById('kontak_penyewa').required = true;
                document.getElementById('id_anggota').required = false;
            } else {
                document.getElementById('form_umum').style.display = 'none';
                document.getElementById('form_anggota').style.display = 'block';
                document.getElementById('nama_penyewa').required = false;
                document.getElementById('kontak_penyewa').required = false;
                document.getElementById('id_anggota').required = true;
            }
        }
        // Panggil saat halaman dimuat
        window.onload = togglePenyewa;
    </script>
</head>
<body>
    <div class="container">
        <h2>Penyewaan Barang</h2>
        
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo $_GET['status'] === 'success' ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>
        
        <h3>Form Penyewaan</h3>
        <form method="POST">
            <div class="form-group">
                <label>Tipe Penyewa:</label>
                <select name="tipe_penyewa" id="tipe_penyewa" onchange="togglePenyewa()" required>
                    <option value="umum">Umum</option>
                    <option value="anggota">Anggota</option>
                </select>
            </div>

            <div id="form_umum">
                <div class="form-group">
                    <label>Nama Penyewa:</label>
                    <input type="text" name="nama_penyewa" id="nama_penyewa">
                </div>
                <div class="form-group">
                    <label>Kontak Penyewa:</label>
                    <input type="text" name="kontak_penyewa" id="kontak_penyewa">
                </div>
            </div>

            <div id="form_anggota" style="display:none">
                <div class="form-group">
                    <label>Pilih Anggota:</label>
                    <select name="id_anggota" id="id_anggota">
                        <option value="">Pilih Anggota</option>
                        <?php while($row = $anggota->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>">
                                <?php echo htmlspecialchars($row['nama']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Alat:</label>
                <select name="id_alat" required>
                    <option value="">Pilih Alat</option>
                    <?php while($row = $alat->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($row['nama_alat']); ?> (Stok: <?php echo $row['jumlah']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Tanggal Sewa:</label>
                <input type="date" name="tanggal_sewa" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
              <div class="form-group">
                <label>Tanggal Kembali:</label>
                <input type="date" name="tanggal_kembali">
            </div>
            
            <div class="form-group">
                <label>Jumlah:</label>
                <input type="number" name="jumlah" min="1" required>
            </div>
            
            <div class="form-group">
                <label>Biaya:</label>
                <input type="number" name="biaya" min="0" step="1000" required>
            </div>
            <button type="submit">Tambah Penyewaan</button>
        </form>
        
        <h3>Daftar Penyewaan Aktif</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Penyewa</th>
                    <th>Kontak</th>
                    <th>Tipe</th>
                    <th>Nama Alat</th>
                    <th>Tanggal Sewa</th>
                    <th>Tanggal Kembali</th>
                    <th>Jumlah</th>
                    <th>Biaya</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($row = $result->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['nama_penyewa']); ?></td>
                    <td><?php echo htmlspecialchars($row['kontak']); ?></td>
                    <td><?php echo htmlspecialchars($row['tipe_penyewa']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_alat']); ?></td>
                    <td>
                        <?php 
                        if (!empty($row['tanggal_sewa']) && $row['tanggal_sewa'] !== '0000-00-00') {
                            echo date('d/m/Y', strtotime($row['tanggal_sewa']));
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td>
                        <?php 
                        if (!empty($row['tanggal_kembali']) && $row['tanggal_kembali'] !== '0000-00-00') {
                            echo date('d/m/Y', strtotime($row['tanggal_kembali']));
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td><?php echo $row['jumlah']; ?></td>
                    <td>Rp <?php echo number_format($row['biaya'], 0, ',', '.'); ?></td>
                    <td>
                        <?php if ($row['status'] === 'disewa'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="kembalikan_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" onclick="return confirm('Tandai sebagai sudah dikembalikan?')">Belum</button>
                            </form>
                        <?php else: ?>
                            Sudah
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit-barang-penyewaan.php?id=<?php echo $row['id']; ?>">Edit</a>
                        <a href="hapus-penyewaan.php?id=<?php echo $row['id']; ?>" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus penyewaan ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <br>
        <a href="riwayat-penyewaan-barang.php">Lihat Riwayat Penyewaan</a>
        <br>
        <a href="../dashboard.php">Kembali ke Dashboard</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>
