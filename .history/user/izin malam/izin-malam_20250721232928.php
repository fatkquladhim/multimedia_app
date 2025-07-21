<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$id_anggota = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT tanggal, jam_izin, jam_selesai_izin, alasan FROM izin_malam WHERE id_anggota = ? ORDER BY tanggal DESC');
$stmt->bind_param('i', $id_anggota);
$stmt->execute();
$izin = $stmt->get_result();

include '../header_beckend.php';
include '../header.php';
?>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-10">

    <div class="container mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-center">Riwayat Izin Malam</h2>

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
                                <td colspan="4" class="py-3 px-4 text-center text-sm text-gray-500">Belum ada pengajuan izin malam.</td>
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
