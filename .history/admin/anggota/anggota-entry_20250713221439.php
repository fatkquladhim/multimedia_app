<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}
?>
<h2>Tambah Anggota</h2>
<form method="post" action="anggota-proses.php" enctype="multipart/form-data">
    <h3>Data Anggota:</h3>
    <input type="text" name="nama" placeholder="Nama" required><br>
    <input type="file" name="foto" accept="image/*" required><br>
    <input type="text" name="alamat" placeholder="Alamat"><br>
    <input type="email" name="email" placeholder="Email"><br>
    <input type="text" name="no_hp" placeholder="No HP"><br>
    <h3>Data Akun Login:</h3>
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Simpan</button>
</form>
<p><a href="anggota.php">Kembali ke Daftar Anggota</a></p>
