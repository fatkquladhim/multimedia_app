<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=uang_keluar.xls");

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Query data pengeluaran
$q_pengeluaran = "SELECT tanggal, jumlah, keterangan FROM uang_keluar ORDER BY tanggal ASC";
$r_pengeluaran = $conn->query($q_pengeluaran);

// Output Excel
echo "<table border='1'>";
echo "<tr><th>Tanggal</th><th>Jumlah</th><th>Keterangan</th></tr>";
while ($row = $r_pengeluaran->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['tanggal']) . "</td>";
    echo "<td>Rp " . number_format($row['jumlah'], 0, ',', '.') . "</td>";
    echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>
