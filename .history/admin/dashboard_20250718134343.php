<?php
session_start();

// Security: Regenerate session ID on first access or after login for better security
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Security: Session timeout
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) { // 30 minutes inactivity
    session_unset();
    session_destroy();
    header('Location: ../auth/login.php');
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time stamp

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Include database configuration
require_once '../includes/db_config.php';

// Constants for limits and pagination
define('TASKS_REVIEW_LIMIT', 3);
define('IZIN_MALAM_LIMIT', 4);
define('ANGGOTA_PER_PAGE', 10);

// Using mysqli_report for better error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = null; // Initialize connection variable

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");

    // --- Statistics (using prepared statements for consistency) ---
    $user_count = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    $user_count = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    $anggota_count = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM anggota");
    $stmt->execute();
    $anggota_count = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    $tugas_count = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM tugas");
    $stmt->execute();
    $tugas_count = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    $izin_malam_count = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM izin_malam");
    $stmt->execute();
    $izin_malam_count = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    $izin_nugas_count = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM izin_nugas");
    $stmt->execute();
    $izin_nugas_count = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    // --- Query tasks awaiting review ---
    $stmt_tugas_review = $conn->prepare("SELECT t.id, t.judul AS nama_tugas, t.deskripsi, u.username,
                                        tj.file_jawaban, tj.tanggal_submit, tj.id_user
                                        FROM tugas t
                                        JOIN tugas_jawaban tj ON t.id = tj.id_tugas
                                        JOIN users u ON tj.id_user = u.id
                                        WHERE t.status = 'pending_review' LIMIT ?");
    $stmt_tugas_review->bind_param("i", TASKS_REVIEW_LIMIT);
    $stmt_tugas_review->execute();
    $tugas_reviews = $stmt_tugas_review->get_result();

    // --- Query members with night permits ---
    $stmt_izin_malam = $conn->prepare("SELECT a.id, a.nama, im.tanggal,
                                      DATE_FORMAT(im.jam_izin, '%H:%i') as jam_izin,
                                      DATE_FORMAT(im.jam_selesai_izin, '%H:%i') as jam_selesai_izin,
                                      im.alasan, im.status
                                      FROM izin_malam im
                                      JOIN anggota a ON im.id_anggota = a.id
                                      WHERE im.tanggal >= CURDATE()
                                      ORDER BY im.tanggal, im.jam_izin LIMIT ?");
    $stmt_izin_malam->bind_param("i", IZIN_MALAM_LIMIT);
    $stmt_izin_malam->execute();
    $izin_malam_anggota = $stmt_izin_malam->get_result();

    // --- Query all members with pagination ---
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = ANGGOTA_PER_PAGE;
    $offset = ($page - 1) * $per_page;

    $total_anggota = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM anggota");
    $stmt->execute();
    $total_anggota = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    $total_pages = ceil($total_anggota / $per_page);

    $stmt_all_anggota = $conn->prepare("SELECT id, nama, email, no_hp FROM anggota
                                       ORDER BY nama LIMIT ?, ?");
    $stmt_all_anggota->bind_param("ii", $offset, $per_page);
    $stmt_all_anggota->execute();
    $all_anggota = $stmt_all_anggota->get_result();

} catch (mysqli_sql_exception $e) {
    error_log("Database error in dashboard.php: " . $e->getMessage());
    // Redirect to a generic error page instead of dying with a message
    header('Location: /error.php'); // Assuming you have an error.php page
    exit;
} finally {
    if ($conn) { // Check if connection was successfully established before closing
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eduhouse - Learning Dashboard</title>
    <!-- Security: Content Security Policy -->
    <?php header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.tailwindcss.com; style-src 'self' https://cdnjs.cloudflare.com/css/all.min.css; img-src 'self' https://via.placeholder.com https://placehold.co;"); ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-md">
            <div class="p-4">
                <h1 class="text-xl font-bold">Dashboard</h1>
            </div>
            <ul class="mt-4">
                <li><a href="dashboard.php" class="block p-4 hover:bg-gray-200">Home</a></li>
                <li><a href="tugas.php" class="block p-4 hover:bg-gray-200">Tugas</a></li>
                <li><a href="anggota.php" class="block p-4 hover:bg-gray-200">Anggota</a></li>
                <li><a href="izin.php" class="block p-4 hover:bg-gray-200">Izin</a></li>
                <li><a href="../auth/logout.php" class="block p-4 hover:bg-gray-200">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <h2 class="text-2xl font-bold mb-4">Dashboard Overview</h2>
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded shadow">
                    <h3 class="font-bold">Total Users</h3>
                    <p><?php echo $user_count; ?></p>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <h3 class="font-bold">Total Anggota</h3>
                    <p><?php echo $anggota_count; ?></p>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <h3 class="font-bold">Total Tugas</h3>
                    <p><?php echo $tugas_count; ?></p>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <h3 class="font-bold">Total Izin Malam</h3>
                    <p><?php echo $izin_malam_count; ?></p>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <h3 class="font-bold">Total Izin Nugas</h3>
                    <p><?php echo $izin_nugas_count; ?></p>
                </div>
            </div>

            <h2 class="text-xl font-bold mt-6">Tugas User Review</h2>
            <table class="min-w-full bg-white border border-gray-300 mt-4">
                <thead>
                    <tr>
                        <th class="border px-4 py-2">ID</th>
                        <th class="border px-4 py-2">Nama Tugas</th>
                        <th class="border px-4 py-2">Deskripsi</th>
                        <th class="border px-4 py-2">Username</th>
                        <th class="border px-4 py-2">File Jawaban</th>
                        <th class="border px-4 py-2">Tanggal Submit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $tugas_reviews->fetch_assoc()): ?>
                        <tr>
                            <td class="border px-4 py-2"><?php echo $row['id']; ?></td>
                            <td class="border px-4 py-2"><?php echo $row['nama_tugas']; ?></td>
                            <td class="border px-4 py-2"><?php echo $row['deskripsi']; ?></td>
                            <td class="border px-4 py-2"><?php echo $row['username']; ?></td>
                            <td class="border px-4 py-2"><a href="<?php echo $row['file_jawaban']; ?>" target="_blank">View</a></td>
                            <td class="border px-4 py-2"><?php echo $row['tanggal_submit']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <h2 class="text-xl font-bold mt-6">Anggota yang Izin Malam</h2>
            <table class="min-w-full bg-white border border-gray-300 mt-4">
                <thead>
                    <tr>
                        <th class="border px-4 py-2">ID</th>
                        <th class="border px-4 py-2">Nama</th>
                        <th class="border px-4 py-2">Tanggal</th>
                        <th class="border px-4 py-2">Jam Izin</th>
                        <th class="border px-4 py-2">Jam Selesai</th>
                        <th class="border px-4 py-2">Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $izin_malam_anggota->fetch_assoc()): ?>
                        <tr>
                            <td class="border px-4 py-2"><?php echo $row['id']; ?></td>
                            <td class="border px-4 py-2"><?php echo $row['nama']; ?></td>
                            <td class="border px-4 py-2"><?php echo $row['tanggal']; ?></td>
                            <td class="border px-4 py-2"><?php echo $row['jam_izin']; ?></td>
                            <td class="border px-4 py-2"><?php echo $row['jam_selesai_izin']; ?></td>
                            <td class="border px-4 py-2"><?php echo $row['alasan']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <h2 class="text-xl font-bold mt-6">Daftar Anggota</h2>
            <table class="min-w-full bg-white border border-gray-300 mt-4">
                <thead>
                    <tr>
                        <th class="border px-4 py-2">ID</th>
                        <th class="border px-4 py-2">Nama</th>
                        <th class="border px-4 py-2">Email</th>
                        <th class="border px-4 py-2">No HP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $all_anggota->fetch_assoc()): ?>
                        <tr>
                            <td class="border px-4 py-2"><?php echo $row['id']; ?></td>
                            <td class="border px-4 py-2"><?php echo $row['nama']; ?></td>
                            <td class="border px-4 py-2"><?php echo $row['email']; ?></td>
                            <td class="border px-4 py-2"><?php echo $row['no_hp']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-4">
                <nav aria-label="Page navigation">
                    <ul class="flex justify-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="mx-1">
                                <a href="?page=<?php echo $i; ?>" class="px-3 py-1 border rounded <?php echo ($i == $page) ? 'bg-blue-500 text-white' : 'bg-white text-blue-500'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</body>

</html>
