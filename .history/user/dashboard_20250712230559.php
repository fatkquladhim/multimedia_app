<?php
session_start();
require_once '../includes/db_config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Query statistik tugas
$total_tugas_query = "SELECT COUNT(*) AS total FROM tugas WHERE id_penerima_tugas = '$user_id'";
$total_tugas_result = mysqli_query($conn, $total_tugas_query);
$total_tugas = mysqli_fetch_assoc($total_tugas_result)['total'];

$riwayat_tugas_query = "SELECT COUNT(*) AS total FROM tugas_jawaban WHERE id_user = '$user_id'";
$riwayat_tugas_result = mysqli_query($conn, $riwayat_tugas_query);
$riwayat_tugas = mysqli_fetch_assoc($riwayat_tugas_result)['total'];

// Query tugas terbaru
$tugas_terbaru_query = "SELECT * FROM tugas WHERE id_penerima_tugas = '$user_id' ORDER BY deadline DESC LIMIT 5";
$tugas_terbaru_result = mysqli_query($conn, $tugas_terbaru_query);

// Query riwayat tugas terbaru
$riwayat_terbaru_query = "SELECT * FROM tugas_jawaban WHERE id_user = '$user_id' ORDER BY id DESC LIMIT 5";
$riwayat_terbaru_result = mysqli_query($conn, $riwayat_terbaru_query);

// Query izin malam terbaru
$izin_malam_query = "SELECT izin_malam.*, anggota.nama FROM izin_malam JOIN anggota ON izin_malam.id_anggota = anggota.id ORDER BY izin_malam.id DESC LIMIT 5";
$izin_malam_result = mysqli_query($conn, $izin_malam_query);

// Query izin nugas terbaru
$izin_nugas_query = "SELECT izin_nugas.*, anggota.nama FROM izin_nugas JOIN anggota ON izin_nugas.id_anggota = anggota.id ORDER BY izin_nugas.id DESC LIMIT 5";
$izin_nugas_result = mysqli_query($conn, $izin_nugas_query);

// Query portfolio terbaru
$portfolio_query = "SELECT tugas_jawaban.*, tugas.judul FROM tugas_jawaban JOIN tugas ON tugas_jawaban.id_tugas = tugas.id WHERE tugas_jawaban.id_user = '$user_id' ORDER BY tugas_jawaban.id DESC LIMIT 5";
$portfolio_result = mysqli_query($conn, $portfolio_query);

// Query profile
$profile_query = "SELECT * FROM profile WHERE id_user = '$user_id' LIMIT 1";
$profile_result = mysqli_query($conn, $profile_query);
$profile = mysqli_fetch_assoc($profile_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        .dashboard-container { max-width: 600px; margin: 40px auto; }
        .stat-item { border: 1px solid #ccc; padding: 16px; margin-bottom: 8px; border-radius: 8px; }
        .feature-links { margin-top: 20px; }
        .feature-links a { display: block; margin: 5px 0; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Selamat Datang, <?php echo htmlspecialchars($profile['nama_lengkap']); ?>!</h2>
        <div class="stat-item">
            <strong>Total Tugas:</strong> <?php echo $total_tugas; ?>
        </div>
        <div class="stat-item">
            <strong>Riwayat Tugas:</strong> <?php echo $riwayat_tugas; ?>
        </div>

        <h3>Fitur Lainnya</h3>
        <div class="feature-links">
            <a href="izin malam/izin-malam.php">Lihat Izin Malam</a>
            <a href="izin nugas/izin-nugas.php">Lihat Izin Nugas</a>
            <a href="portfolio/portfolio.php">Lihat Portfolio</a>
            <a href="profile/profile_view.php">Lihat Profil</a>
            <a href="tugas/tugas_user.php">Lihat Tugas</a>
            <a href="tugas/riwayat_tugas.php">Lihat Riwayat Tugas</a>
        </div>

        <h3>Tugas Terbaru</h3>
        <?php if ($tugas_terbaru_result && mysqli_num_rows($tugas_terbaru_result) > 0): ?>
            <ul>
                <?php while ($task = mysqli_fetch_assoc($tugas_terbaru_result)): ?>
                    <li>
                        <strong>Judul:</strong> <?php echo htmlspecialchars($task['judul']); ?> - <strong>Deadline:</strong> <?php echo htmlspecialchars($task['deadline']); ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Tidak ada tugas terbaru.</p>
        <?php endif; ?>

        <h3>Riwayat Tugas Terbaru</h3>
        <?php if ($riwayat_terbaru_result && mysqli_num_rows($riwayat_terbaru_result) > 0): ?>
            <ul>
                <?php while ($history = mysqli_fetch_assoc($riwayat_terbaru_result)): ?>
                    <li>
                        <strong>Judul:</strong> <?php echo htmlspecialchars($history['judul']); ?> - <a href="../uploads/tugas_jawaban/<?php echo htmlspecialchars($history['file']); ?>" target="_blank">Download</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Belum ada riwayat tugas terbaru.</p>
        <?php endif; ?>

        <h3>Izin Malam Terbaru</h3>
        <?php if ($izin_malam_result && mysqli_num_rows($izin_malam_result) > 0): ?>
            <ul>
                <?php while ($izin = mysqli_fetch_assoc($izin_malam_result)): ?>
                    <li>
                        <strong>Keperluan:</strong> <?php echo htmlspecialchars($izin['keperluan']); ?> - <strong>Tanggal:</strong> <?php echo htmlspecialchars($izin['tanggal']); ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Tidak ada izin malam terbaru.</p>
        <?php endif; ?>

        <h3>Izin Nugas Terbaru</h3>
        <?php if ($izin_nugas_result && mysqli_num_rows($izin_nugas_result) > 0): ?>
            <ul>
                <?php while ($izin = mysqli_fetch_assoc($izin_nugas_result)): ?>
                    <li>
                        <strong>Keperluan:</strong> <?php echo htmlspecialchars($izin['keperluan']); ?> - <strong>Tanggal:</strong> <?php echo htmlspecialchars($izin['tanggal']); ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Tidak ada izin nugas terbaru.</p>
        <?php endif; ?>

        <h3>Portfolio Terbaru</h3>
        <?php if ($portfolio_result && mysqli_num_rows($portfolio_result) > 0): ?>
            <ul>
                <?php while ($item = mysqli_fetch_assoc($portfolio_result)): ?>
                    <li>
                        <strong>Judul:</strong> <?php echo htmlspecialchars($item['judul']); ?> - <strong>Deskripsi:</strong> <?php echo htmlspecialchars($item['deskripsi']); ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Belum ada portfolio terbaru.</p>
        <?php endif; ?>
    </div>
</body>
</html>