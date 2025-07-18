<?php
session_start();
require_once '../../includes/db_config.php';

// Periksa login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

// Inisialisasi variabel
$errors = [];
$success = false;

// Proses form jika data dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_anggota = $_SESSION['user_id'];
    $tanggal = $_POST['tanggal'] ?? '';
    $jam_izin = $_POST['jam_izin'] ?? '';
    $jam_selesai_izin = $_POST['jam_selesai_izin'] ?? '';
    $alasan = $_POST['alasan'] ?? '';

    // Validasi input
    if (empty($tanggal)) {
        $errors[] = 'Tanggal izin harus diisi';
    }
    if (empty($jam_izin)) {
        $errors[] = 'Jam izin harus diisi';
    }
    if (empty($alasan) || strlen($alasan) < 10) {
        $errors[] = 'Alasan harus diisi minimal 10 karakter';
    }

    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        $stmt = $conn->prepare('INSERT INTO izin_malam 
                               (id_anggota, tanggal, jam_izin, jam_selesai_izin, alasan, status) 
                               VALUES (?, ?, ?, ?, ?, "Menunggu")');
        $stmt->bind_param('issss', $id_anggota, $tanggal, $jam_izin, $jam_selesai_izin, $alasan);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = 'Gagal menyimpan data izin: ' . $conn->error;
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Ambil data profil untuk ditampilkan di header
$profile_name = "User";
$profile_photo = "default_profile.jpg";

if (isset($_SESSION['user_id'])) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $stmt = $conn->prepare('SELECT nama_lengkap, foto FROM profile WHERE id_user = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($fetched_name, $fetched_photo);
    
    if ($stmt->fetch()) {
        $profile_name = htmlspecialchars($fetched_name);
        $profile_photo = htmlspecialchars($fetched_photo);
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Izin Malam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { transition: width 0.3s ease-in-out; }
        .sidebar-text { transition: opacity 0.3s ease-in-out, margin-left 0.3s ease-in-out; }
        .sidebar-nav-item { justify-content: flex-start; }
        .sidebar.collapsed .sidebar-nav-item { justify-content: center; }
        .sidebar.collapsed .sidebar-text { opacity: 0; width: 0; overflow: hidden; white-space: nowrap; pointer-events: none; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <?php include '../sidebar.php'; ?>

        <div class="flex-1 p-4 overflow-y-auto">
            <header class="bg-white border-b border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <button id="sidebarToggle" class="p-2 text-gray-600 hover:text-gray-800 focus:outline-none mr-4">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Pengajuan Izin Malam</h1>
                            <p class="text-gray-600"><?php echo date('l, d F Y'); ?></p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <div class="w-10 h-10 rounded-full overflow-hidden">
                            <img src="../../uploads/profiles/<?php echo $profile_photo; ?>" 
                                 alt="Foto Profil" 
                                 class="w-full h-full object-cover">
                        </div>
                        <div>
                            <span class="font-medium text-gray-800"><?php echo $profile_name; ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4">
                <!-- Notifikasi Sukses/Error -->
                <?php if ($success): ?>
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        Pengajuan izin malam berhasil dikirim!
                    </div>
                    <a href="izin-malam.php" class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke daftar izin
                    </a>
                <?php else: ?>
                    <!-- Formulir Pengajuan -->
                    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold mb-4">Formulir Izin Malam</h2>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                                <ul class="list-disc pl-5">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-4">
                                <label for="tanggal" class="block text-gray-700 mb-2">Tanggal Izin</label>
                                <input type="date" id="tanggal" name="tanggal" 
                                       value="<?php echo htmlspecialchars($_POST['tanggal'] ?? ''); ?>" 
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="jam_izin" class="block text-gray-700 mb-2">Jam Keluar</label>
                                    <input type="time" id="jam_izin" name="jam_izin" 
                                           value="<?php echo htmlspecialchars($_POST['jam_izin'] ?? ''); ?>" 
                                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="jam_selesai_izin" class="block text-gray-700 mb-2">Jam Kembali</label>
                                    <input type="time" id="jam_selesai_izin" name="jam_selesai_izin" 
                                           value="<?php echo htmlspecialchars($_POST['jam_selesai_izin'] ?? ''); ?>" 
                                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="alasan" class="block text-gray-700 mb-2">Alasan Izin</label>
                                <textarea id="alasan" name="alasan" rows="4" 
                                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($_POST['alasan'] ?? ''); ?></textarea>
                            </div>

                            <div class="flex justify-between items-center">
                                <a href="izin-malam.php" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                                </a>
                                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                    <i class="fas fa-paper-plane mr-2"></i>Kirim Permohonan
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        // Sidebar toggle script
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');
        const sidebarNavItems = document.querySelectorAll('.sidebar-nav-item');

        let isSidebarOpen = true;

        sidebarToggle.addEventListener('click', () => {
            isSidebarOpen = !isSidebarOpen;
            
            if (isSidebarOpen) {
                sidebar.classList.remove('w-20', 'collapsed');
                sidebar.classList.add('w-64');
                
                sidebarTexts.forEach(text => text.classList.remove('opacity-0', 'pointer-events-none'));
                sidebarNavItems.forEach(item => {
                    item.classList.remove('justify-center', 'px-0');
                    item.classList.add('justify-start', 'px-4');
                });
                
                sidebarToggle.querySelector('i').classList.replace('fa-arrow-right', 'fa-bars');
            } else {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20', 'collapsed');
                
                sidebarTexts.forEach(text => text.classList.add('opacity-0', 'pointer-events-none'));
                sidebarNavItems.forEach(item => {
                    item.classList.remove('justify-start', 'px-4');
                    item.classList.add('justify-center', 'px-0');
                });
                
                sidebarToggle.querySelector('i').classList.replace('fa-bars', 'fa-arrow-right');
            }
        });
        
        // Set default date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('tanggal').value = today;
        });
    </script>
</body>
</html>
