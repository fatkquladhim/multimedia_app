<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}
?>
<h2>Edit Profil</h2>
<form method="post" action="profile_store.php" enctype="multipart/form-data">
    <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="text" name="alamat" placeholder="Alamat"><br>
    <input type="text" name="no_hp" placeholder="No HP"><br>
    <input type="file" name="foto" accept="image/*"><br>
    <button type="submit">Update</button>
</form>
