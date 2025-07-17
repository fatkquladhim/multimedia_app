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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Tugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        a { text-decoration: none; color: #007bff; }
        a:hover { text-decoration: underline; }
        .sidebar {
            transition: width 0.3s ease-in-out;
        }

        .sidebar-text {
            transition: opacity 0.3s ease-in-out, margin-left 0.3s ease-in-out;
        }

        .sidebar-nav-item {
            justify-content: flex-start;
        }

        .sidebar.collapsed .sidebar-nav-item {
            justify-content: center;
        }

        .sidebar.collapsed .sidebar-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
        }

        .sidebar.collapsed .sidebar-logo-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
        }

        .sidebar.collapsed .sidebar-logo-icon {
            margin-right: 0 !important;
        }

        .sidebar.collapsed .sidebar-create-button .sidebar-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
        }

        .sidebar.collapsed .sidebar-create-button i {
            margin-right: 0 !important;
        }

        .sidebar.collapsed .sidebar-upgrade-section {
            opacity: 0;
            height: 0;
            overflow: hidden;
            padding-top: 0;
            padding-bottom: 0;
            margin-top: 0;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden ">
        <div class="flex h-screen">
            <?php include '../sidebar.php'; ?>

            <div class="flex-1 p-2">
                <header class="bg-white border-b border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <button id="sidebarToggle" class="p-2 text-gray-600 hover:text-gray-800 focus:outline-none mr-4">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                            <div>
                                <p class="text-gray-600"><?php echo date('l, d F Y'); ?></p>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="p-6">
                    <h2 class="text-xl font-bold mb-4">Riwayat Tugas</h2>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
                            <thead>
                                <tr>
                                    <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Judul Tugas</th>
                                    <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Deadline Tugas</th>
                                    <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal Submit</th>
                                    <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">File Jawaban</th>
                                    <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                    <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nilai</th>
                                    <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Komentar Admin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
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
                                            if ($row['tugas_status'] == 'diperiksa') {
                                                echo '<span class="px-2 py-1 text-xs font-semibold leading-tight text-green-700 bg-green-100 rounded-full">Sudah Dinilai</span>';
                                            } elseif ($row['tugas_status'] == 'selesai') {
                                                echo '<span class="px-2 py-1 text-xs font-semibold leading-tight text-orange-700 bg-orange-100 rounded-full">Menunggu Penilaian</span>';
                                            } else {
                                                echo '<span class="px-2 py-1 text-xs font-semibold leading-tight text-gray-700 bg-gray-100 rounded-full">' . htmlspecialchars($row['tugas_status']) . '</span>';
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
                </main>
            </div>
        </div>
    </div>
    <script>
        // Sidebar toggle logic (same as dashboard.php)
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');
        const sidebarLogoText = document.querySelector('.sidebar-logo-text');
        const sidebarLogoIcon = document.querySelector('.sidebar-logo-icon');
        const sidebarNavItems = document.querySelectorAll('.sidebar-nav-item');
        const sidebarCreateButton = document.querySelector('.sidebar-create-button');
        const sidebarUpgradeSection = document.querySelector('.sidebar-upgrade-section');

        let isSidebarOpen = true;

        sidebarToggle.addEventListener('click', () => {
            if (isSidebarOpen) {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20', 'collapsed');

                sidebarTexts.forEach(text => { text.classList.add('opacity-0', 'pointer-events-none'); });
                if (sidebarLogoText) sidebarLogoText.classList.add('opacity-0', 'pointer-events-none');
                if (sidebarUpgradeSection) sidebarUpgradeSection.classList.add('opacity-0', 'h-0', 'p-0', 'mt-0', 'pointer-events-none');

                if (sidebarLogoIcon) {
                    sidebarLogoIcon.classList.remove('space-x-2');
                    sidebarLogoIcon.classList.add('mx-auto');
                }
                sidebarNavItems.forEach(item => {
                    item.classList.remove('space-x-3', 'px-4');
                    item.classList.add('justify-center', 'px-0');
                });
                if (sidebarCreateButton) {
                    sidebarCreateButton.classList.remove('space-x-2');
                    sidebarCreateButton.classList.add('justify-center');
                    if (sidebarCreateButton.querySelector('button')) {
                        sidebarCreateButton.querySelector('button').classList.remove('space-x-2');
                        sidebarCreateButton.querySelector('button').classList.add('justify-center');
                    }
                }

                sidebarToggle.querySelector('i').classList.replace('fa-bars', 'fa-arrow-right');

            } else {
                sidebar.classList.remove('w-20', 'collapsed');
                sidebar.classList.add('w-64');

                sidebarTexts.forEach(text => { text.classList.remove('opacity-0', 'pointer-events-none'); });
                if (sidebarLogoText) sidebarLogoText.classList.remove('opacity-0', 'pointer-events-none');
                if (sidebarUpgradeSection) sidebarUpgradeSection.classList.remove('opacity-0', 'h-0', 'p-0', 'mt-0', 'pointer-events-none');

                if (sidebarLogoIcon) {
                    sidebarLogoIcon.classList.remove('mx-auto');
                    sidebarLogoIcon.classList.add('space-x-2');
                }
                sidebarNavItems.forEach(item => {
                    item.classList.remove('justify-center', 'px-0');
                    item.classList.add('space-x-3', 'px-4');
                });
                if (sidebarCreateButton) {
                    sidebarCreateButton.classList.remove('justify-center');
                    sidebarCreateButton.classList.add('space-x-2');
                    if (sidebarCreateButton.querySelector('button')) {
                        sidebarCreateButton.querySelector('button').classList.remove('justify-center');
                        sidebarCreateButton.querySelector('button').classList.add('space-x-2');
                    }
                }

                sidebarToggle.querySelector('i').classList.replace('fa-arrow-right', 'fa-bars');
            }
            isSidebarOpen = !isSidebarOpen;
        });
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
