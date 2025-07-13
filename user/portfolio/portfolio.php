<?php
require_once '../../includes/db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}

// Ambil data hasil pengerjaan tugas user
$sql = "SELECT tugas_jawaban.*, tugas.judul, users.nama_lengkap FROM tugas_jawaban JOIN tugas ON tugas_jawaban.id_tugas = tugas.id JOIN users ON tugas_jawaban.id_user = users.id ORDER BY tugas_jawaban.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Portfolio Hasil Pengerjaan Tugas</title>
    <style>
        body { font-family: Arial; background: #f7f7f7; }
        .container { max-width: 800px; margin: 40px auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px #0001; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #007bff; color: #fff; }
        tr:nth-child(even) { background: #f2f2f2; }
        h2 { margin-bottom: 10px; }
        img { max-width: 120px; max-height: 80px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Portfolio Hasil Pengerjaan Tugas</h2>
    <table>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Judul Tugas</th>
            <th>File/Gambar</th>
            <th>Tanggal Upload</th>
        </tr>
        <?php if ($result && $result->num_rows > 0): $no = 1; ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                    <td><?= htmlspecialchars($row['judul']) ?></td>
                    <td>
                        <?php if (!empty($row['file_jawaban'])): ?>
                            <a href="../../uploads/tugas_jawaban/<?= htmlspecialchars($row['file_jawaban']) ?>" target="_blank">
                                <img src="../../uploads/tugas_jawaban/<?= htmlspecialchars($row['file_jawaban']) ?>" alt="File Jawaban">
                            </a>
                        <?php else: ?>
                            Tidak ada file
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['tanggal_upload']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">Belum ada hasil tugas yang diupload.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
