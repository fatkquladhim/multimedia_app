<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil data alat yang akan diedit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM alat WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $alat = $result->fetch_assoc();

    if (!$alat) {
        header("Location: daftar-alat.php?status=error&message=Alat tidak ditemukan");
        exit;
    }
    $stmt->close();
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nama_alat = $_POST['nama_alat'];
    $jumlah = $_POST['jumlah'];
    $kondisi = $_POST['kondisi'];
    $kelompok = $_POST['kelompok'];
    $milik = $_POST['milik'];

    $stmt = $conn->prepare("UPDATE alat SET nama_alat = ?, jumlah = ?, kondisi = ?, kelompok = ?, milik = ? WHERE id = ?");
    $stmt->bind_param("sisssi", $nama_alat, $jumlah, $kondisi, $kelompok, $milik, $id);

    if ($stmt->execute()) {
        header("Location: daftar-alat.php?status=success&message=Alat berhasil diupdate");
    } else {
        header("Location: edit-barang.php?id=$id&status=error&message=Gagal mengupdate alat");
    }

    $stmt->close();
    exit;
}
include '../header_beckend.php';
include '../header.php';
?>

<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">✏️ Edit Alat</h2>
                <p class="text-gray-600">Perbarui informasi alat: <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($alat['nama_alat']); ?></span></p>
            </div>
        </div>

        <?php
        if (isset($_GET['status']) && isset($_GET['message'])) {
            $status = $_GET['status'];
            $message = htmlspecialchars($_GET['message']);
            $alertClass = $status === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
            
            echo "<div class='mb-6 p-4 rounded-lg border $alertClass flex items-center'>
                    <svg class='w-5 h-5 mr-3' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path>
                    </svg>
                    $message
                  </div>";
        }
        ?>

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="id" value="<?php echo $alat['id']; ?>">
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="text-red-500">*</span> Nama Alat
                        </label>
                        <input type="text" name="nama_alat" required
                               value="<?php echo htmlspecialchars($alat['nama_alat']); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="text-red-500">*</span> Jumlah
                        </label>
                        <input type="number" name="jumlah" min="0" required
                               value="<?php echo $alat['jumlah']; ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="text-red-500">*</span> Kondisi
                        </label>
                        <select name="kondisi" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                            <option value="Baik" <?php echo ($alat['kondisi'] == 'Baik') ? 'selected' : ''; ?>>Baik</option>
                            <option value="Rusak Ringan" <?php echo ($alat['kondisi'] == 'Rusak Ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                            <option value="Rusak Berat" <?php echo ($alat['kondisi'] == 'Rusak Berat') ? 'selected' : ''; ?>>Rusak Berat</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kelompok</label>
                        <input type="text" name="kelompok"
                               value="<?php echo htmlspecialchars($alat['kelompok']); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Milik</label>
                        <input type="text" name="milik"
                               value="<?php echo htmlspecialchars($alat['milik']); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-6">
                    <button type="submit"
                            class="flex-1 inline-flex justify-center items-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Update Alat
                    </button>
                    <a href="daftar-alat.php"
                       class="flex-1 inline-flex justify-center items-center px-6 py-3 bg-gray-500 text-white font-semibold rounded-lg hover:bg-gray-600 transition-all duration-200 shadow-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
<?php
include '../footer.php';
$conn->close();
?>
