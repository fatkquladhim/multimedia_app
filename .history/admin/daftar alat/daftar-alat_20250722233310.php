<?php
// daftar-alat.php - Enhanced with Tailwind CSS
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil daftar alat
$query = "SELECT * FROM alat ORDER BY nama_alat";
$result = $conn->query($query);
include '../header_beckend.php';
include '../header.php';
?>

<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header Section -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">📋 Daftar Alat</h2>
                    <p class="text-gray-600">Kelola inventaris alat dengan mudah dan efisien</p>
                </div>
                <a href="tambah-barang.php" 
                   class="inline-flex items-center px-6 py-3 bg-primary text-black font-semibold rounded-lg hover:bg-secondary transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Alat Baru
                </a>
            </div>
        </div>

        <?php
        // Tampilkan pesan status jika ada
        if (isset($_GET['status']) && isset($_GET['message'])) {
            $status = $_GET['status'];
            $message = htmlspecialchars($_GET['message']);
            $alertClass = $status === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
            $iconPath = $status === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
            
            echo "<div class='mb-6 p-4 rounded-lg border $alertClass flex items-center'>
                    <svg class='w-5 h-5 mr-3' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='$iconPath'></path>
                    </svg>
                    $message
                  </div>";
        }
        ?>

        <!-- Table Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-primary to-secondary text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-black font-semibold uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-left text-black font-semibold uppercase tracking-wider">Nama Alat</th>
                            <th class="px-6 py-4 text-left text-black font-semibold uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-4 text-left text-black font-semibold uppercase tracking-wider">Kondisi</th>
                            <th class="px-6 py-4 text-left text-black font-semibold uppercase tracking-wider">Kelompok</th>
                            <th class="px-6 py-4 text-left text-black font-semibold uppercase tracking-wider">Milik</th>
                            <th class="px-6 py-4 text-center text-black font-semibold uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        $no = 1;
                        while ($row = $result->fetch_assoc()):
                            $kondisiClass = match($row['kondisi']) {
                                'Baik' => 'bg-green-100 text-green-800',
                                'Rusak Ringan' => 'bg-yellow-100 text-yellow-800',
                                'Rusak Berat' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        ?>
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo $no++; ?></td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_alat']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        <?php echo $row['jumlah']; ?> unit
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $kondisiClass; ?>">
                                        <?php echo htmlspecialchars($row['kondisi']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['kelompok']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['milik']); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="edit-barang.php?id=<?php echo $row['id']; ?>" 
                                           class="inline-flex items-center px-3 py-1.5 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600 transition-all duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </a>
                                        <a href="hapus-barang.php?id=<?php echo $row['id']; ?>"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus alat ini?')"
                                           class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition-all duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer Stats -->
        <div class="mt-8 bg-white rounded-xl shadow-lg p-6">
            <div class="text-center text-gray-600">
                <p class="text-sm">Total: <span class="font-semibold text-primary"><?php echo mysqli_num_rows($result); ?></span> alat terdaftar</p>
            </div>
        </div>
    </div>
<?php
// Sertakan footer
include '../footer.php';
$conn->close();
?>