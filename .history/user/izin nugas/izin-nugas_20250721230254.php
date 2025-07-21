<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$id_anggota = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT tanggal, jam_izin, jam_selesai_izin, alasan FROM izin_nugas WHERE id_anggota = ? ORDER BY tanggal DESC');
$stmt->bind_param('i', $id_anggota);
$stmt->execute();
$izin = $stmt->get_result();

include '../header_beckend.php';
include '../header.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Izin nugas</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .table-header {
            @apply bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider;
        }
        .table-row {
            @apply border-b border-gray-200 hover:bg-gray-50;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-10">

    <div class="container mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-center">Riwayat Izin nugas</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
                    <thead>
                        <tr>
                            <th class="py-3 px-4 table-header">Tanggal</th>
                            <th class="py-3 px-4 table-header">Jam Izin</th>
                            <th class="py-3 px-4 table-header">Jam Kembali</th>
                            <th class="py-3 px-4 table-header">Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($izin->num_rows > 0): ?>
                            <?php while ($row = $izin->fetch_assoc()) { ?>
                                <tr class="table-row">
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['tanggal']); ?></td>
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['jam_izin']); ?></td>
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['jam_selesai_izin']); ?></td>
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['alasan']); ?></td>
                                </tr>
                            <?php } ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-3 px-4 text-center text-sm text-gray-500">Belum ada pengajuan izin nugas.</td>
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
include '../footer.php';
$conn->close();
?>
