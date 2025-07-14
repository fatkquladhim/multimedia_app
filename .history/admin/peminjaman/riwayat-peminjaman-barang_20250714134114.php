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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Peminjaman Barang</title>
    <style>
        .container { padding: 20px; }
        .table-container { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; }
        th { background-color: #f5f5f5; }
        .alert { padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Riwayat Peminjaman Barang</h2>
        
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
                        <th>Nama Peminjam</th>
                        <th>Kontak</th>
                        <th>Tipe</th>
                        <th>Nama Alat</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Jumlah</th>
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
                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                        <td><?php echo $row['tanggal_kembali'] ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-'; ?></td>
                        <td><?php echo $row['jumlah']; ?></td>
                        <td>
                           
                        <a href="edit-barang-peminjaman.php?id=<?php echo $row['id']; ?>">Edit</a>
                        <a href="hapus-peminjaman.php?id=<?php echo $row['id']; ?>" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus peminjaman ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <a href="peminjaman-barang.php">Kembali ke Daftar Peminjaman</a>
        <br>
        <a href="../dashboard.php">Kembali ke Dashboard</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>
