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
$conn->close();
?>

    <div class="container">
        <h2>Riwayat Penyewaan Barang</h2>
        
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo $_GET['status'] === 'success' ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="table-container">
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
                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_sewa'])); ?></td>
                        <td><?php echo $row['tanggal_kembali'] ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-'; ?></td>
                        <td><?php echo $row['jumlah']; ?></td>
                        <td>Rp <?php echo number_format($row['biaya'], 0, ',', '.'); ?></td>
                        <td>
                           <a href="edit-barang-penyewaan.php?id=<?php echo $row['id']; ?>">Edit</a>
                        <a href="hapus-penyewaan.php?id=<?php echo $row['id']; ?>" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus penyewaan ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <a href="penyewaan-barang.php">Kembali ke Daftar Penyewaan</a>
        <br>
        <a href="../dashboard.php">Kembali ke Dashboard</a>
    </div>
<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
?>
