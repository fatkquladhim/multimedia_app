<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}
include '../header_beckend.php';
include '../header.php';
?>

<div class="min-h-screen  from-blue-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                        Tambah Anggota Baru
                    </h1>
                    <p class="text-gray-600 text-lg">Daftarkan anggota baru beserta data akun loginnya</p>
                </div>
                <a href="anggota.php" 
                   class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Daftar Anggota
                </a>
            </div>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <form method="post" action="anggota-proses.php" enctype="multipart/form-data" class="p-8">
                
                <!-- Data Anggota Section -->
                <div class="mb-10">
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-4 -mx-8 -mt-8 mb-8 border-b border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Data Anggota
                        </h3>
                        <p class="text-gray-600 mt-1 text-sm">Informasi personal anggota yang akan didaftarkan</p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Nama Lengkap *</label>
                            <div class="relative">
                                <input type="text" 
                                       name="nama" 
                                       placeholder="Masukkan nama lengkap" 
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 pl-12">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Email</label>
                            <div class="relative">
                                <input type="email" 
                                       name="email" 
                                       placeholder="contoh@email.com"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 pl-12">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6 mt-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Alamat</label>
                            <div class="relative">
                                <input type="text" 
                                       name="alamat" 
                                       placeholder="Masukkan alamat lengkap"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 pl-12">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">No HP</label>
                            <div class="relative">
                                <input type="text" 
                                       name="no_hp" 
                                       placeholder="08xxxxxxxxxx"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 pl-12">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Profil *</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 transition-colors duration-200 bg-gray-50">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <input type="file" 
                                       name="foto" 
                                       accept="image/*" 
                                       required
                                       class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent mb-2">
                                <p class="text-sm text-gray-500">Format: JPG, PNG, GIF. Maksimal 2MB</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Akun Login Section -->
                <div class="border-t border-gray-200 pt-8">
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 -mx-8 mb-8 rounded-xl border border-purple-100">
                        <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Data Akun Login
                        </h3>
                        <p class="text-gray-600 mt-1 text-sm">Informasi akun untuk login ke sistem</p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Username *</label>
                            <div class="relative">
                                <input type="text" 
                                       name="username" 
                                       placeholder="Masukkan username" 
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 pl-12">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Password *</label>
                            <div class="relative">
                                <input type="password" 
                                       name="password" 
                                       placeholder="Masukkan password" 
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 pl-12">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-8 border-t border-gray-200 mt-8">
                    <div class="flex flex-col sm:flex-row gap-4 justify-end">
                        <a href="anggota.php" 
                           class="px-8 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all duration-300 text-center">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Simpan Data Anggota
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Info Card -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-2xl p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-semibold text-blue-800">Informasi Penting</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Field bertanda (*) wajib diisi</li>
                            <li>Username harus unik dan tidak boleh sama dengan yang sudah ada</li>
                            <li>Password minimal 6 karakter untuk keamanan yang baik</li>
                            <li>Foto profil akan digunakan untuk identifikasi anggota</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Sertakan footer
include '../footer.php';
$conn->close();
?>