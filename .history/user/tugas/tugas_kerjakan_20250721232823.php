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
    header('Location: ../dashboard.php?status=error&message=ID tugas tidak valid.');
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
    header('Location:../dashboard.php?status=error&message=Tugas tidak ditemukan atau bukan tugas Anda.');
    exit;
}

if ($tugas['jawaban_id']) {
    header('Location: ../dashboard.php?status=info&message=Tugas ini sudah Anda kerjakan.');
    exit;
}

if (strtotime($tugas['deadline']) < strtotime(date('Y-m-d'))) {
    header('Location:../dashboard.php?status=error&message=Tugas ini sudah melewati deadline.');
    exit;
}
include '../header_beckend.php';
include '../header.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kerjakan Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
                .glass-header {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.9) 0%, rgba(59, 130, 246, 0.9) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .glass-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center py-10">

    <div class="container mx-auto px-4">
        <?php
        if (isset($_GET['status'])) {
            echo '<div class="message ' . htmlspecialchars($_GET['status']) . '">' . htmlspecialchars($_GET['message']) . '</div>';
        }
        ?>

        <div class="bg-white p-8 rounded-lg shadow-xl max-w-lg mx-auto">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Detail Tugas</h2>
            <div class="task-details mb-6 space-y-2">
                <p><strong class="text-gray-700">Judul:</strong> <?php echo htmlspecialchars($tugas['judul']); ?></p>
                <p><strong class="text-gray-700">Deskripsi:</strong> <?php echo nl2br(htmlspecialchars($tugas['deskripsi'])); ?></p>
                <p><strong class="text-gray-700">Deadline:</strong> <?php echo date('d/m/Y', strtotime($tugas['deadline'])); ?></p>
            </div>

            <form method="post" action="tugas_kirim_store.php" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="id_tugas" value="<?php echo $id_tugas; ?>">
                <div>
                    <label for="file_jawaban" class="block text-sm font-medium text-gray-700 mb-1">Upload File Jawaban (PDF/JPG/PNG, Max 5MB):</label>
                    <input type="file" id="file_jawaban" name="file_jawaban" accept=".pdf,.jpg,.jpeg,.png,.mp4,.mov,.avi,.mkv,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required class="form-input-file">
                    <small class="text-gray-500 mt-1 block">Ukuran file maksimal 40MB.</small>
                </div>
                <div class="flex justify-center pt-4">
                    <button type="submit" class="login-btn w-full py-2 font-semibold rounded-full focus:outline-none glass-header px-3 py-2 text-white relative z-10">Kirim Jawaban</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php
// Sertakan footer
include '../footer.php';
$conn->close();
?>
