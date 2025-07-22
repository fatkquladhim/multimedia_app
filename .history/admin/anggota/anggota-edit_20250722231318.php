<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}
require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Hapus anggota
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM anggota WHERE id = $id");
    echo '<div class="mb-6 p-4 rounded-xl border bg-red-50 border-red-200 text-red-800 flex items-center">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">Anggota berhasil dihapus.</span>
          </div>';
}

// Proses tambah/edit anggota
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $email = $_POST['email'] ?? '';
    $no_hp = $_POST['no_hp'] ?? '';

    // Proses upload foto
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (in_array($_FILES['foto']['type'], $allowed_types) && $_FILES['foto']['size'] <= $max_size) {
            $foto = 'uploads/' . basename($_FILES['foto']['name']);
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], '../../' . $foto)) {
                error_log('Gagal memindahkan file foto: ' . $_FILES['foto']['name']);
                echo '<div class="mb-6 p-4 rounded-xl border bg-red-50 border-red-200 text-red-800 flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-medium">Gagal mengunggah foto.</span>
                      </div>';
                exit;
            }
        } else {
            error_log('File foto tidak valid: ' . $_FILES['foto']['name']);
            echo '<div class="mb-6 p-4 rounded-xl border bg-red-50 border-red-200 text-red-800 flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">File foto tidak valid.</span>
                  </div>';
            exit;
        }
    }

    if (isset($_POST['id']) && $_POST['id']) {
        // Edit
        $id = $_POST['id'];
        $stmt = $conn->prepare('UPDATE anggota SET nama=?, foto=?, alamat=?, email=?, no_hp=? WHERE id=?');
        $stmt->bind_param('sssssi', $nama, $foto, $alamat, $email, $no_hp, $id);
        $stmt->execute();
        $stmt->close();
        echo '<div class="mb-6 p-4 rounded-xl border bg-green-50 border-green-200 text-green-800 flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium">Data anggota berhasil diupdate.</span>
              </div>';
    } else {
        // Tambah
        $stmt = $conn->prepare('INSERT INTO anggota (nama, foto, alamat, email, no_hp) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $nama, $foto, $alamat, $email, $no_hp);
        $stmt->execute();
        $stmt->close();
        echo '<div class="mb-6 p-4 rounded-xl border bg-green-50 border-green-200 text-green-800 flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium">Anggota baru berhasil ditambahkan.</span>
              </div>';
    }
}

// Ambil data anggota untuk edit
$edit_data = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM anggota WHERE id = $id");
    $edit_data = $result->fetch_assoc();
}

include '../header_beckend.php';
include '../header.php';
?>

<div class="min-h-screen from-blue-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                        <?php echo $edit_data ? 'Edit Anggota' : 'Tambah Anggota'; ?>
                    </h1>
                    <p class="text-gray-600 text-lg">
                        <?php echo $edit_data ? 'Update data anggota yang sudah ada' : 'Tambahkan anggota baru ke sistem'; ?>
                    </p>
                </div>
                <a href="anggota.php" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 border border-gray-100">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 -mx-8 -mt-8 mb-8 rounded-t-2xl border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Form Data Anggota
                </h3>
            </div>

            <form method="post" enctype="multipart/form-data" class="space-y-6">
                <?php if ($edit_data) echo '<input type="hidden" name="id" value="'.$edit_data['id'].'">'; ?>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                        <div class="relative">
                            <input type="text" 
                                   name="nama" 
                                   placeholder="Masukkan nama lengkap" 
                                   value="<?php echo $edit_data['nama'] ?? ''; ?>" 
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 pl-12">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <div class="relative">
                            <input type="email" 
                                   name="email" 
                                   placeholder="contoh@email.com" 
                                   value="<?php echo $edit_data['email'] ?? ''; ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 pl-12">
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat</label>
                        <div class="relative">
                            <input type="text" 
                                   name="alamat" 
                                   placeholder="Masukkan alamat lengkap" 
                                   value="<?php echo $edit_data['alamat'] ?? ''; ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 pl-12">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">No HP</label>
                        <div class="relative">
                            <input type="text" 
                                   name="no_hp" 
                                   placeholder="08xxxxxxxxxx" 
                                   value="<?php echo $edit_data['no_hp'] ?? ''; ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 pl-12">
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Profil</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors duration-200">
                        <input type="file" 
                               name="foto" 
                               accept="image/*" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-2">Format: JPG, PNG, GIF. Maksimal 2MB</p>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" 
                            class="w-full md:w-auto px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <?php echo $edit_data ? 'Update Data' : 'Simpan Data'; ?>
                    </button>
                </div>
            </form>
        </div>      
    </div>
</main>
<?php
// Sertakan footer
include '../footer.php';
$conn->close();
?>