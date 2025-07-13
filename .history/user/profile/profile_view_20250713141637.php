<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}
?>
<?php
require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$stmt = $conn->prepare('SELECT nama_lengkap, email, alamat, no_hp, foto FROM profile WHERE id_user = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($nama_lengkap, $email, $alamat, $no_hp, $foto);
$stmt->fetch();
$stmt->close();
$conn->close();
?>
<h2>Profil Saya</h2>
<p>Username: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
<p>Nama Lengkap: <?php echo htmlspecialchars($nama_lengkap); ?></p>
<p>Email: <?php echo htmlspecialchars($email); ?></p>
<p>Alamat: <?php echo htmlspecialchars($alamat); ?></p>
<p>No HP: <?php echo htmlspecialchars($no_hp); ?></p>
<?php if ($foto) { ?>
    <p><img src="../../uploads/profiles/<?php echo htmlspecialchars($foto); ?>" alt="Foto Profil" width="120"></p>
<?php } ?>
<a href="profile_edit.php">Edit Profil</a>
