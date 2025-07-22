<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil daftar user untuk dropdown
$users = $conn->query("SELECT id, username, nama_lengkap FROM users WHERE role = 'user'");
include '../header_beckend.php';
include '../header.php';
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center space-x-3 mb-2">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Beri Tugas Baru</h2>
            </div>
            <p class="text-gray-600">Berikan tugas baru kepada pengguna dengan mengisi form di bawah ini</p>
        </div>

        <!-- Status Messages -->
        <?php if (isset($_GET['status'])): ?>
            <div class="mb-6">
                <?php if ($_GET['status'] == 'success'): ?>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="text-green-800 font-medium"><?php echo htmlspecialchars($_GET['message']); ?></div>
                    </div>
                <?php else: ?>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="text-red-800 font-medium"><?php echo htmlspecialchars($_GET['message']); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <form action="beri_tugas_store.php" method="POST" class="space-y-6">
                <!-- Judul Tugas -->
                <div>
                    <label for="judul" class="block text-sm font-semibold text-gray-700 mb-2">
                        Judul Tugas <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="judul" 
                        name="judul" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 placeholder-gray-400"
                        placeholder="Masukkan judul tugas..."
                    >
                </div>

                <!-- Deskripsi Tugas -->
                <div>
                    <label for="deskripsi" class="block text-sm font-semibold text-gray-700 mb-2">
                        Deskripsi Tugas <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="deskripsi" 
                        name="deskripsi" 
                        rows="6" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 placeholder-gray-400 resize-vertical"
                        placeholder="Jelaskan detail tugas yang akan diberikan..."
                    ></textarea>
                </div>

                <!-- Deadline -->
                <div>
                    <label for="deadline" class="block text-sm font-semibold text-gray-700 mb-2">
                        Deadline <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="deadline" 
                        name="deadline" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                    >
                </div>

                <!-- Pilih User -->
                <div>
                    <label for="id_penerima_tugas" class="block text-sm font-semibold text-gray-700 mb-2">
                        Pilih User <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="id_penerima_tugas" 
                        name="id_penerima_tugas" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                    >
                        <option value="">Pilih User</option>
                        <?php while($user = $users->fetch_assoc()): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['username'] . ' - ' . ($user['nama_lengkap'] ?? 'Nama tidak ada')); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        Beri Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
?>