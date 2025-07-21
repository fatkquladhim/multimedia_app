<?php
require_once '../includes/db_config.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("ID tidak valid.");
}

// Ambil data legalisasi dan user terkait

// Ambil data legalisasi, user, dan foto profile
$stmt = $conn->prepare("
    SELECT l.*, u.username, p.photo AS profile_photo
    FROM legalizations l
    JOIN users u ON l.user_id = u.id
    LEFT JOIN profiles p ON p.user_id = u.id
    WHERE l.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Data legalisasi tidak ditemukan.");
}

$data = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Legalisasi Laptop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-shadow { box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-100 min-h-screen">
    
    <!-- Navigation -->
    <!-- <nav class="bg-white shadow-sm border-b border-gray-200 no-print">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-center">
                <div class="flex items-center space-x-4">
                    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 flex items-center">
                        <i class="fas fa-print mr-2"></i>
                        Cetak
                    </button>
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 flex items-center">
                        <i class="fas fa-download mr-2"></i>
                        Download PDF
                    </button>
                </div>
            </div>
        </div>
    </nav> -->

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            
            <!-- Header Card -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8 print-shadow">
                <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 px-8 py-6 text-white relative">
                    <div class="absolute inset-0 bg-black bg-opacity-10"></div>
                    <div class="relative z-10">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex flex-col items-center md:items-start">
                                <?php if (!empty($data['profile_photo'])): ?>
                                <div class="mb-4 flex justify-center md:justify-start w-full">
                                    <div class="aspect-square bg-gray-50 rounded-full overflow-hidden w-32 h-32 md:w-32 md:h-32 flex items-center justify-center border-4 border-purple-400 shadow-lg">
                                        <img src="../uploads/profiles/<?= htmlspecialchars($data['profile_photo']) ?>" alt="Foto Pemilik" class="w-full h-full object-cover">
                                    </div>
                                </div>
                                <?php endif; ?>
                                <h1 class="text-3xl font-bold flex items-center mb-2">
                                    <i class="fas fa-certificate mr-3"></i>
                                    Sertifikat Legalisasi
                                </h1>
                                <p class="text-blue-100 text-lg">
                                    Laptop milik <strong style="color:#ffc100;"><?= htmlspecialchars($data['username']) ?></strong>
                                </p>
                            </div>
                            <div class="mt-4 md:mt-0 text-center">
                                <div class="bg-white bg-opacity-20 rounded-lg px-4 py-2 backdrop-blur-sm">
                                    <p class="text-sm font-semibold">Status</p>
                                    <div class="flex items-center justify-center mt-1">
                                        <i class="fas fa-check-circle text-green-300 mr-2"></i>
                                        <span class="font-bold">VERIFIED</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Verification Badge -->
                <div class="px-8 py-4 bg-green-50 border-l-4 border-green-500">
                    <div class="flex items-center">
                        <i class="fas fa-shield-check text-green-600 text-xl mr-3"></i>
                        <div>
                            <p class="font-semibold text-green-800">Laptop Terverifikasi</p>
                            <p class="text-green-600 text-sm">Dokumen ini telah diverifikasi dan sah secara resmi</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Information Grid -->
            <div class="grid lg:grid-cols-3 gap-8">
                
                <!-- Left Column - Laptop Details -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Laptop Information -->
                    <div class="bg-white rounded-2xl shadow-lg p-8 print-shadow">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-laptop text-blue-600 mr-3"></i>
                            Informasi Laptop
                        </h2>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition duration-200">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-tag text-blue-500 w-5 mr-3"></i>
                                        <span class="text-sm font-semibold text-gray-600">Merek Laptop</span>
                                    </div>
                                    <p class="text-gray-800 font-bold text-lg pl-8"><?= htmlspecialchars($data['laptop_brand']) ?></p>
                                </div>
                                
                                <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition duration-200">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-hashtag text-green-500 w-5 mr-3"></i>
                                        <span class="text-sm font-semibold text-gray-600">Nomor Seri</span>
                                    </div>
                                    <p class="text-gray-800 font-mono text-sm pl-8"><?= htmlspecialchars($data['serial_number']) ?></p>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition duration-200">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-user text-purple-500 w-5 mr-3"></i>
                                        <span class="text-sm font-semibold text-gray-600">Pemilik</span>
                                    </div>
                                    <p class="text-gray-800 font-medium pl-8"><?= htmlspecialchars($data['username']) ?></p>
                                </div>
                                
                                <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition duration-200">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-calendar-alt text-orange-500 w-5 mr-3"></i>
                                        <span class="text-sm font-semibold text-gray-600">Tanggal Legalisasi</span>
                                    </div>
                                    <p class="text-gray-800 font-medium pl-8"><?= htmlspecialchars($data['approval_date']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="bg-white rounded-2xl shadow-lg p-8 print-shadow">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-file-alt text-indigo-600 mr-3"></i>
                            Deskripsi
                        </h2>
                        <div class="bg-gray-50 rounded-xl p-6">
                            <p class="text-gray-700 leading-relaxed">
                                <?= nl2br(htmlspecialchars($data['description'])) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Approval Information -->
                    <div class="bg-white rounded-2xl shadow-lg p-8 print-shadow">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-user-shield text-red-600 mr-3"></i>
                            Informasi Approval
                        </h2>
                        
                        <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-6 border border-green-200">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-white text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="font-semibold text-gray-800">Disetujui oleh Administrator</p>
                                    <!-- <p class="text-sm text-gray-600">ID Admin: <?= htmlspecialchars($data['approved_by']) ?></p> -->
                                </div>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Tanggal Approval:</span>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($data['approval_date']) ?></p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Status:</span>
                                    <p class="font-semibold text-green-600">✓ APPROVED</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Photo & QR Code -->
                <div class="space-y-6">
                    
                    <!-- Laptop Photo & Profile Photo -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 print-shadow">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-camera text-pink-600 mr-3"></i>
                            Foto Laptop
                        </h3>
                        <div class="flex flex-col items-center gap-6">
                            <?php if (!empty($data['photo'])): ?>
                            <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden w-48 h-48 flex items-center justify-center border-4 border-blue-200 shadow-md">
                                <img src="../uploads/legalisasi/<?= htmlspecialchars($data['photo']) ?>" alt="Foto Laptop" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            </div>
                            <p class="text-gray-600 text-sm mt-2 mb-4">Foto Laptop</p>
                            <?php endif; ?>
                            <!-- <?php if (!empty($data['profile_photo'])): ?>
                            <div class="aspect-square bg-gray-50 rounded-full overflow-hidden w-32 h-32 flex items-center justify-center border-4 border-purple-200 shadow">
                                <img src="../uploads/profiles/<?= htmlspecialchars($data['profile_photo']) ?>" alt="Foto Pemilik" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            </div>
                            <p class="text-gray-600 text-sm mt-2">Foto Pemilik</p> -->
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <!-- <div class="bg-white rounded-2xl shadow-lg p-6 text-center print-shadow">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center justify-center">
                            <i class="fas fa-qrcode text-gray-600 mr-3"></i>
                            QR Code Verifikasi
                        </h3>
                        <?php if (!empty($data['qr_code'])): ?>
                        <div class="bg-gray-50 p-4 rounded-xl">
                            <div class="w-32 h-32 bg-white mx-auto rounded-lg shadow-sm flex items-center justify-center">
                                <img src="<?= htmlspecialchars($data['qr_code']) ?>" alt="QR Code" class="w-full h-full rounded-lg">
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-3">Scan untuk verifikasi online</p>
                        <?php endif; ?>
                    </div> -->

                    <!-- Verification Steps -->
                    <!-- <div class="bg-white rounded-2xl shadow-lg p-6 print-shadow no-print">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-list-alt text-blue-600 mr-3"></i>
                            Langkah Verifikasi
                        </h3>
                        <ol class="list-decimal list-inside text-gray-700">
                            <li>Scan QR Code di atas menggunakan aplikasi pemindai QR.</li>
                            <li>Kunjungi tautan yang muncul untuk memverifikasi status legalisasi.</li>
                            <li>Masukkan informasi yang diperlukan jika diminta.</li>
                            <li>Anda akan menerima konfirmasi status verifikasi melalui email.</li>
                        </ol>
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white shadow-inner mt-8 no-print">
        <div class="container mx-auto px-4 py-4 text-center">
            <p class="text-gray-600 text-sm">&copy; powered by media tech annur2almurtadlo</p>
        </div>
    </footer>
</body>
</html>
