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
        .form-input-file {
            @apply mt-1 block w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer focus:outline-none;
        }
        .btn-primary {
            @apply bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow-md transition duration-200 ease-in-out;
        }
        .message {
            @apply p-3 mb-4 rounded-md text-sm;
        }
        .message.success {
            @apply bg-green-100 text-green-700;
        }
        .message.error {
            @apply bg-red-100 text-red-700;
        }
        .message.info {
            @apply bg-blue-100 text-blue-700;
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
                    <input type="file" id="file_jawaban" name="file_jawaban" accept="application/pdf,image/jpeg,image/png" required class="form-input-file">
                    <small class="text-gray-500 mt-1 block">Ukuran file maksimal 5MB.</small>
                </div>
                <div class="flex justify-center pt-4">
                    <button type="submit" class="btn-primary w-full">Kirim Jawaban</button>
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
