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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kerjakan Tugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .task-details { background-color: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .task-details strong { display: inline-block; width: 100px; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input[type="file"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 0.375rem;
            box-sizing: border-box;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-primary { background-color: #28a745; color: white; border: none; }
        .btn-primary:hover { background-color: #218838; }
        .btn-secondary { background-color: #6B7280; color: white; border: none; }
        .btn-secondary:hover { background-color: #4B5563; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
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
                                <a href="tugas_user.php" class="btn btn-secondary flex items-center justify-center">Kembali ke Tugas Saya</a>
                            </div>
                        </form>
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

<?php $conn->close(); ?>
