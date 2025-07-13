<?php
require_once '../../includes/db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_anggota = $conn->real_escape_string($_POST['id_anggota']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);
    $alasan = $conn->real_escape_string($_POST['alasan']);

    $sql = "INSERT INTO izin_malam (id_anggota, tanggal, alasan) VALUES ('$id_anggota', '$tanggal', '$alasan')";
    if ($conn->query($sql) === TRUE) {
        $success = 'Pengajuan izin malam berhasil disimpan!';
    } else {
        $error = 'Gagal menyimpan: ' . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengajuan Izin Malam</title>
    <style>
        body { font-family: Arial; background: #f7f7f7; }
        .container { max-width: 400px; margin: 40px auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px #0001; }
        input, textarea, select { width: 100%; padding: 8px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .msg { margin-bottom: 16px; padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <h2>Form Pengajuan Izin Malam</h2>
    <?php if ($success): ?>
        <div class="msg success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="msg error"><?= $error ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Anggota</label>
        <select name="id_anggota" required>
            <?php
            $result = $conn->query("SELECT id, nama FROM anggota");
            while ($row = $result->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
            <?php endwhile; ?>
        </select>
        <label>Tanggal Izin</label>
        <input type="date" name="tanggal" required>
        <label>Alasan</label>
        <textarea name="alasan" rows="3" required></textarea>
        <button type="submit">Ajukan Izin</button>
    </form>
</div>
</body>
</html>
