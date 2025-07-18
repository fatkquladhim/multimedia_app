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
include '../header.php'; // Path relatif dari 'anggota/'
?>

    <div class="container">
        <h2>Peminjaman Barang</h2>
        
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo $_GET['status'] === 'success' ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>
        
        <h3>Form Peminjaman</h3>
        <form method="POST">
            <div class="form-group">
                <label>Tipe Peminjam:</label>
                <select name="tipe_peminjam" id="tipe_peminjam" onchange="togglePeminjam()" required>
                    <option value="umum">Umum</option>
                    <option value="anggota">Anggota</option>
                </select>
            </div>

            <div id="form_umum">
                <div class="form-group">
                    <label>Nama Peminjam:</label>
                    <input type="text" name="nama_peminjam" id="nama_peminjam">
                </div>
                <div class="form-group">
                    <label>Kontak Peminjam:</label>
                    <input type="text" name="kontak_peminjam" id="kontak_peminjam">
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
                <label>Tanggal Pinjam:</label>
                <input type="date" name="tanggal_pinjam" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label>Tanggal Kembali (opsional):</label>
                <input type="date" name="tanggal_kembali">
            </div>
            
            <div class="form-group">
                <label>Jumlah:</label>
                <input type="number" name="jumlah" min="1" required>
            </div>
            
            <button type="submit">Tambah Peminjaman</button>
        </form>
        
        <h3>Daftar Peminjaman Aktif</h3>
        <table>
            <thead>
            <tr>
                <th>No</th>
                <th>Nama Peminjam</th>
                <th>Kontak</th>
                <th>Tipe</th>
                <th>Nama Alat</th>
                <th>Tanggal Pinjam</th>
                <th>Tanggal Kembali</th>
                <th>Jumlah</th>
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
                    <td><?php echo htmlspecialchars($row['nama_peminjam']); ?></td>
                    <td><?php echo htmlspecialchars($row['kontak']); ?></td>
                    <td><?php echo htmlspecialchars($row['tipe_peminjam']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_alat']); ?></td>
                    <td>
                        <?php 
                        if (!empty($row['tanggal_pinjam']) && $row['tanggal_pinjam'] !== '0000-00-00') {
                            echo date('d/m/Y', strtotime($row['tanggal_pinjam']));
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
                    <td>
                        <?php if ($row['status'] === 'dipinjam'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="kembalikan_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" onclick="return confirm('Tandai sebagai sudah dikembalikan?')">Belum</button>
                            </form>
                        <?php else: ?>
                            Sudah
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit-barang-peminjaman.php?id=<?php echo $row['id']; ?>">Edit</a>
                        <a href="hapus-peminjaman.php?id=<?php echo $row['id']; ?>" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus peminjaman ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <br>
        <a href="riwayat-peminjaman-barang.php">Lihat Riwayat Peminjaman</a>
        <br>
        <a href="../dashboard.php">Kembali ke Dashboard</a>
    </div>

 
