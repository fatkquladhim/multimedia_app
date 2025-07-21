<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$id_user = $_SESSION['user_id'];

$stmt = $conn->prepare('
    SELECT
        tj.id,
        t.judul,
        t.deadline,
        tj.file_jawaban,
        tj.nilai,
        tj.komentar,
        tj.tanggal_submit,
        t.status as tugas_status
    FROM tugas_jawaban tj
    JOIN tugas t ON tj.id_tugas = t.id
    WHERE tj.id_user = ?
    ORDER BY tj.tanggal_submit DESC
');
$stmt->bind_param('i', $id_user);
$stmt->execute();
$result = $stmt->get_result();
include '../header_beckend.php';
include '../header.php';
?>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-10">

    <div class="container mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-4 text-center">Portfolio</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
                    <thead>
                        <tr>
                            <th class="py-3 px-4 table-header">Judul Tugas</th>
                            <th class="py-3 px-4 table-header">Deadline Tugas</th>
                            <th class="py-3 px-4 table-header">Tanggal Submit</th>
                            <th class="py-3 px-4 table-header">File Jawaban</th>
                            <th class="py-3 px-4 table-header">Status</th>
                            <th class="py-3 px-4 table-header">Nilai</th>
                            <th class="py-3 px-4 table-header">Komentar Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="table-row">
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['judul']); ?></td>
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo date('d/m/Y', strtotime($row['deadline'])); ?></td>
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo date('d/m/Y H:i', strtotime($row['tanggal_submit'])); ?></td>
                                    <td class="py-3 px-4 text-sm text-gray-700">
                                        <?php if ($row['file_jawaban']): ?>
                                            <a href="../../uploads/tugas_jawaban/<?php echo htmlspecialchars($row['file_jawaban']); ?>" target="_blank" class="text-blue-500 hover:underline">Download</a>
                                        <?php else: echo '-'; endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-700">
                                        <?php
                                        if ($row['tugas_status'] == 'selesai') {
                                            echo '<span class="status-badge status-selesai">Sudah Dinilai</span>';
                                        } elseif ($row['tugas_status'] == 'diperiksa') {
                                            echo '<span class="status-badge status-diperiksa">Menunggu Penilaian</span>';
                                        } else {
                                            echo '<span class="status-badge status-default">' . htmlspecialchars($row['tugas_status']) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo $row['nilai'] !== null ? htmlspecialchars($row['nilai']) : '-'; ?></td>
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['komentar'] ?? '-'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="py-3 px-4 text-center text-sm text-gray-500">Anda belum mengirimkan jawaban untuk tugas apapun.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
?>
