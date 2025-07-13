<?php
require_once '../../includes/db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}

// Ambil data riwayat izin malam
$sql = "SELECT izin_malam.*, anggota.nama FROM izin_malam JOIN anggota ON izin_malam.id_anggota = anggota.id ORDER BY tanggal DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Izin Malam</title>
    <style>
        body { font-family: Arial; background: #f7f7f7; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px #0001; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #007bff; color: #fff; }
        tr:nth-child(even) { background: #f2f2f2; }
        h2 { margin-bottom: 10px; }
        a.button { display: inline-block; margin-bottom: 16px; padding: 8px 16px; background: #007bff; color: #fff; border-radius: 4px; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h2>Riwayat Pengajuan Izin Malam</h2>
    <a href="izin-malam-entry.php" class="button">Ajukan Izin Malam</a>
    <table>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Tanggal</th>
            <th>Alasan</th>
        </tr>
        <?php if ($result && $result->num_rows > 0): $no = 1; ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                    <td><?= htmlspecialchars($row['alasan']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">Belum ada data pengajuan izin malam.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
