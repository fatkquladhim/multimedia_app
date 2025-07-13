<?php
require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=uang_masuk.xls");

echo "<table border='1'>";
echo "<tr><th>Tanggal</th><th>Jumlah</th><th>Keterangan</th><th>Status</th></tr>";

$q_manual = "SELECT tanggal, jumlah, keterangan, 'Manual' as status FROM uang_masuk";
$q_auto = "SELECT p.tanggal_kembali as tanggal, p.biaya as jumlah, CONCAT('Otomatis dari penyewaan ID: ', p.id, ' (', IFNULL(p.nama_penyewa, '-'), ')') as keterangan, p.status FROM penyewaan_barang p WHERE p.status='dikembalikan' AND p.biaya > 0";

$result_manual = $conn->query($q_manual);
$result_auto = $conn->query($q_auto);

while ($row = $result_manual->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['tanggal']) . "</td>";
    echo "<td>Rp " . number_format($row['jumlah'], 0, ',', '.') . "</td>";
    echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
    echo "</tr>";
}

while ($row = $result_auto->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['tanggal']) . "</td>";
    echo "<td>Rp " . number_format($row['jumlah'], 0, ',', '.') . "</td>";
    echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
    echo "</tr>";
}

echo "</table>";
$conn->close();
?>
