<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}
?>
<h2>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
<ul>
    <li><a href="profile/profile_view.php">Profil Saya</a></li>
    <li><a href="tugas/tugas_user.php">Tugas Saya</a></li>
    <li><a href="izin malam/izin-malam.php">Izin Malam</a></li>
    <li><a href="izin nugas/izin-nugas.php">Izin Nugas</a></li>
    <li><a href="portfolio/portfolio.php">Portfolio</a></li>
    <li><a href="../auth/logout.php">Logout</a></li>
</ul>
