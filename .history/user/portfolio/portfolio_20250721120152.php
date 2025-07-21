<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$id_user = $_SESSION['user_id'];

// Fetch user profile for display
$profile_name = "User";
$profile_photo = "default_profile.jpg";
$stmt_profile = $conn->prepare('SELECT nama_lengkap, foto FROM profile WHERE id_user = ?');
$stmt_profile->bind_param('i', $id_user);
$stmt_profile->execute();
$stmt_profile->bind_result($fetched_name, $fetched_photo);
if ($stmt_profile->fetch()) {
    $profile_name = htmlspecialchars($fetched_name);
    $profile_photo = htmlspecialchars($fetched_photo);
}
$stmt_profile->close();

// Fetch portfolio items
$stmt = $conn->prepare('SELECT * FROM portfolio WHERE id_user = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $id_user);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio - Multimedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
        }
        .dark .gradient-bg {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
        }
        .dark .glass-effect {
            background: rgba(30, 41, 59, 0.9);
        }
        .hover-scale:hover {
            transform: scale(1.02);
        }
        .portfolio-item {
            transition: all 0.3s ease;
        }
        .portfolio-item:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <?php include '../../includes/header.php'; ?>
    
    <main class="p-4 md:p-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Portfolio Saya</h1>
                <a href="portfolio-entry.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Tambah Portfolio
                </a>
            </div>

            <?php if (isset($_GET['status'])): ?>
                <div class="mb-6 p-4 rounded-xl <?php echo $_GET['status'] == 'success' ? 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-400 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-400 border border-red-200 dark:border-red-800'; ?>">
                    <i class="fas <?php echo $_GET['status'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($item = $result->fetch_assoc()): ?>
                        <div class="portfolio-item bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm hover-scale">
                            <div class="relative h-48 overflow-hidden">
                                <img src="../../uploads/portfolio/<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['judul']); ?>" class="w-full h-full object-cover transition-transform duration-500 hover:scale-110">
                            </div>
                            <div class="p-4">
                                <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2"><?php echo htmlspecialchars($item['judul']); ?></h3>
                                <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-2"><?php echo htmlspecialchars($item['deskripsi']); ?></p>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo date('d M Y', strtotime($item['created_at'])); ?>
                                    </span>
                                    <div class="flex space-x-2">
                                        <a href="portfolio-edit.php?id=<?php echo $item['id']; ?>" class="text-blue-500 hover:text-blue-700 dark:hover:text-blue-400">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="portfolio-delete.php?id=<?php echo $item['id']; ?>" class="text-red-500 hover:text-red-700 dark:hover:text-red-400" onclick="return confirm('Apakah Anda yakin ingin menghapus portfolio ini?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-12">
                        <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-folder-open text-gray-400 dark:text-gray-600 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-2">Portfolio Kosong</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Anda belum memiliki portfolio</p>
                        <a href="portfolio-entry.php" class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-plus mr-2"></i> Tambah Portfolio
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>

