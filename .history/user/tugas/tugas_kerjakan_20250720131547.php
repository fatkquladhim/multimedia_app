<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}
require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$id_user = $_SESSION['user_id'];
$id_tugas = $_GET['id'] ?? null;

if (!$id_tugas) {
    header('Location: tugas_user.php?status=error&message=ID tugas tidak valid.');
    exit;
}

$stmt = $conn->prepare('
    SELECT
        t.id,
        t.judul,
        t.deskripsi,
        t.deadline,
        t.status,
        tj.id as jawaban_id,
        tj.file_jawaban
    FROM tugas t
    LEFT JOIN tugas_jawaban tj ON t.id = tj.id_tugas AND tj.id_user = ?
    WHERE t.id = ? AND t.id_penerima_tugas = ?
');
$stmt->bind_param('iii', $id_user, $id_tugas, $id_user);
$stmt->execute();
$result = $stmt->get_result();
$tugas = $result->fetch_assoc();
$stmt->close();

if (!$tugas) {
    header('Location: tugas_user.php?status=error&message=Tugas tidak ditemukan atau bukan tugas Anda.');
    exit;
}

if ($tugas['jawaban_id']) {
    header('Location: tugas_user.php?status=info&message=Tugas ini sudah Anda kerjakan.');
    exit;
}

if (strtotime($tugas['deadline']) < strtotime(date('Y-m-d'))) {
    header('Location: tugas_user.php?status=error&message=Tugas ini sudah melewati deadline.');
    exit;
}
include '../header_beckend.php';
include '../header.php';
?>
<main class="p-6">
    <?php
    if (isset($_GET['status'])) {
        echo '<div class="message ' . htmlspecialchars($_GET['status']) . '">' . htmlspecialchars($_GET['message']) . '</div>';
    }
    ?>

    <div class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
        <h2 class="text-xl font-bold mb-4">Detail Tugas</h2>
        <div class="task-details mb-6">
            <p class="mb-2"><strong class="text-gray-700">Judul:</strong> <?php echo htmlspecialchars($tugas['judul']); ?></p>
            <p class="mb-2"><strong class="text-gray-700">Deskripsi:</strong> <?php echo nl2br(htmlspecialchars($tugas['deskripsi'])); ?></p>
            <p class="mb-2"><strong class="text-gray-700">Deadline:</strong> <?php echo date('d/m/Y', strtotime($tugas['deadline'])); ?></p>
        </div>

        <form method="post" action="tugas_kirim_store.php" enctype="multipart/form-data">
            <input type="hidden" name="id_tugas" value="<?php echo $id_tugas; ?>">
            <div class="form-group">
                <label for="file_jawaban">Upload File Jawaban (PDF/JPG/PNG, Max 5MB):</label>
                <input type="file" id="file_jawaban" name="file_jawaban" accept="application/pdf,image/jpeg,image/png" required>
                <small class="text-gray-500 mt-1 block">Ukuran file maksimal 5MB.</small>
            </div>
            <div class="flex space-x-4 mt-6">
                <button type="submit" class="btn btn-primary">Kirim Jawaban</button>
            </div>
        </form>
    </div>
    <?php
    // Sertakan footer
    include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
    $conn->close();
    ?>