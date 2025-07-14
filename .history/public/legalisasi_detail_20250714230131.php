<?php
session_start();
require_once '../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Periksa parameter ID dari URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Query data legalisasi beserta data anggota
    $stmt = $conn->prepare("SELECT l.*, a.nama, a.email, a.telepon 
                          FROM legalisasi_laptop l 
                          JOIN anggota a ON l.id_anggota = a.id 
                          WHERE l.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die("Data legalisasi tidak ditemukan");
    }
    
    $data = $result->fetch_assoc();
    $stmt->close();
} else {
    die("ID legalisasi tidak valid");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Legalisasi Laptop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>
    <style>
        .qr-code {
            margin: 20px auto;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 px-6 py-4 text-white">
                <h1 class="text-2xl font-bold">Detail Legalisasi Laptop</h1>
                <p class="text-blue-100">Nomor Registrasi: <?= htmlspecialchars($data['id']) ?></p>
            </div>
            
            <!-- Badge Status -->
            <div class="px-6 py-3 border-b">
                <?php 
                $status_color = [
                    'Baik' => 'bg-green-100 text-green-800',
                    'Rusak' => 'bg-red-100 text-red-800',
                    'Perlu Perbaikan' => 'bg-yellow-100 text-yellow-800'
                ];
                ?>
                <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $status_color[$data['status']] ?>">
                    Status: <?= htmlspecialchars($data['status']) ?>
                </span>
            </div>
            
            <!-- Informasi Lengkap -->
            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <!-- Kolom Kiri -->
                    <div>
                        <h2 class="text-xl font-semibold mb-4 text-gray-800">Informasi Anggota</h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Nama Lengkap</p>
                                <p class="font-medium"><?= htmlspecialchars($data['nama']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="font-medium"><?= htmlspecialchars($data['email']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Telepon</p>
                                <p class="font-medium"><?= htmlspecialchars($data['telepon']) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kolom Kanan -->
                    <div>
                        <h2 class="text-xl font-semibold mb-4 text-gray-800">Detail Laptop</h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Merk</p>
                                <p class="font-medium"><?= htmlspecialchars($data['merk']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tipe</p>
                                <p class="font-medium"><?= htmlspecialchars($data['tipe']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Serial Number</p>
                                <p class="font-medium"><?= htmlspecialchars($data['serial_number']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tanggal Legalisasi</p>
                                <p class="font-medium"><?= date('d F Y', strtotime($data['created_at'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Foto Bukti -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">Foto Bukti</h2>
                    <div class="border rounded-lg p-4 flex justify-center">
                        <?php if ($data['file_bukti']): ?>
                            <img src="../../uploads/legalisasi/<?= htmlspecialchars($data['file_bukti']) ?>" 
                                 alt="Foto bukti laptop <?= htmlspecialchars($data['merk']) ?> <?= htmlspecialchars($data['serial_number']) ?>"
                                 class="max-h-64">
                        <?php else: ?>
                            <p class="text-gray-500">Foto tidak tersedia</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- QR Code -->
                <div class="border-t pt-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">QR Code Verifikasi</h2>
                    <div class="flex flex-col items-center">
                        <div id="qrcode" class="qr-code"></div>
                        <p class="text-sm text-gray-500 mt-2">Scan QR Code untuk memverifikasi keaslian dokumen</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Generate QR Code
        document.addEventListener('DOMContentLoaded', function() {
            const currentUrl = window.location.href;
            QRCode.toCanvas(
                document.getElementById('qrcode'), 
                currentUrl,
                { width: 200 },
                function(error) {
                    if (error) console.error(error);
                }
            );
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
