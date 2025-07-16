<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$id_user = $_SESSION['user_id'];

// Ambil riwayat tugas yang sudah dijawab oleh user
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
    <title>Pitch.io - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        a { text-decoration: none; color: #007bff; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden ">
        <div class="flex h-screen">
            <!-- Include Sidebar -->
            <?php include '../sidebar.php'; ?>

            <!-- Main Content -->
            <div class="flex-1 p-2">
                <header class="bg-white border-b border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <!-- Sidebar Toggle Button -->
                            <button id="sidebarToggle" class="p-2 text-gray-600 hover:text-gray-800 focus:outline-none mr-4">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                                <p class="text-gray-600">Monday, 02 March 2020</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <button class="p-2 text-gray-600 hover:text-gray-800">
                                <i class="fas fa-envelope text-xl"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:text-gray-800">
                                <i class="fas fa-bell text-xl"></i>
                            </button>
                            <div class="flex items-center space-x-2">
                                <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">FA</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="font-medium text-gray-800">Fatkqul Adhim</span>
                                    <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Konten Dashboard -->
                <h2 class="text-xl font-bold mt-4">Riwayat Tugas</h2>
                <p><a href="tugas_user.php">Kembali ke Tugas Saya</a></p>
                <table>
                    <thead>
                        <tr>
                            <th>Judul Tugas</th>
                            <th>Deadline Tugas</th>
                            <th>Tanggal Submit</th>
                            <th>File Jawaban</th>
                            <th>Status</th>
                            <th>Nilai</th>
                            <th>Komentar Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['judul']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['deadline'])); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_submit'])); ?></td>
                            <td>
                                <?php if ($row['file_jawaban']): ?>
                                    <a href="../../uploads/tugas_jawaban/<?php echo htmlspecialchars($row['file_jawaban']); ?>" target="_blank">Download</a>
                                <?php else: echo '-'; endif; ?>
                            </td>
                            <td>
                                <?php 
                                if ($row['tugas_status'] == 'diperiksa') {
                                    echo 'Sudah Dinilai';
                                } elseif ($row['tugas_status'] == 'selesai') {
                                    echo 'Menunggu Penilaian';
                                } else {
                                    echo htmlspecialchars($row['tugas_status']);
                                }
                                ?>
                            </td>
                            <td><?php echo $row['nilai'] !== null ? htmlspecialchars($row['nilai']) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($row['komentar'] ?? '-'); ?></td>
                        </tr>
                        <?php 
                            endwhile; 
                        else:
                        ?>
                        <tr>
                            <td colspan="7">Anda belum mengirimkan jawaban untuk tugas apapun.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');

        let isSidebarOpen = true; // Initial state: sidebar is open

        sidebarToggle.addEventListener('click', () => {
            if (isSidebarOpen) {
                // Collapse sidebar
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20', 'collapsed');

                // Hide texts
                const sidebarTexts = document.querySelectorAll('.sidebar-text');
                sidebarTexts.forEach(text => {
                    text.classList.add('opacity-0', 'pointer-events-none');
                });

                // Change toggle icon
                sidebarToggle.querySelector('i').classList.replace('fa-bars', 'fa-arrow-right');
            } else {
                // Expand sidebar
                sidebar.classList.remove('w-20', 'collapsed');
                sidebar.classList.add('w-64');

                // Show texts
                const sidebarTexts = document.querySelectorAll('.sidebar-text');
                sidebarTexts.forEach(text => {
                    text.classList.remove('opacity-0', 'pointer-events-none');
                });

                // Change toggle icon
                sidebarToggle.querySelector('i').classList.replace('fa-arrow-right', 'fa-bars');
            }
            isSidebarOpen = !isSidebarOpen; // Toggle the state
        });
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
