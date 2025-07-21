<?php
session_start();
require_once '../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Periksa parameter ID dari URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Query data legalisasi beserta data anggota
    $stmt = $conn->prepare("SELECT l.*, a.nama, a.email, a.no_hp
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            z-index: -1;
        }
        
        .glass-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(31, 38, 135, 0.2);
        }
        
        .glass-header {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.9) 0%, rgba(59, 130, 246, 0.9) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .glass-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .status-badge {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 4px 15px rgba(31, 38, 135, 0.1);
        }
        
        .info-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            transition: all 0.3s ease;
        }
        
        .info-card:hover {
            background: rgba(255, 255, 255, 0.85);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(31, 38, 135, 0.15);
        }
        
        .section-divider {
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.3), transparent);
            height: 1px;
            margin: 2rem 0;
        }
        
        .qr-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 2px solid rgba(59, 130, 246, 0.2);
            box-shadow: 0 8px 25px rgba(31, 38, 135, 0.1);
        }
        
        .image-container {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        
        .floating-element {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .float-1 {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }
        
        .float-2 {
            width: 150px;
            height: 150px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .float-3 {
            width: 80px;
            height: 80px;
            bottom: 15%;
            left: 15%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) scale(1);
                opacity: 0.3;
            }
            50% {
                transform: translateY(-20px) scale(1.05);
                opacity: 0.6;
            }
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }
        
        @media (max-width: 768px) {
            .glass-container {
                margin: 1rem;
                border-radius: 1.5rem;
            }
            
            .floating-element {
                display: none;
            }
        }
        
        .icon {
            filter: drop-shadow(0 2px 4px rgba(59, 130, 246, 0.3));
        }
    </style>
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-element float-1"></div>
    <div class="floating-element float-2"></div>
    <div class="floating-element float-3"></div>

    <div class="container mx-auto px-4 py-8 max-w-5xl relative z-10">
        <div class="glass-container rounded-3xl overflow-hidden">
            <!-- Header dengan Glassmorphism -->
            <div class="glass-header px-8 py-6 text-white relative z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold mb-2 flex items-center">
                            <svg class="w-8 h-8 mr-3 icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H6zm1 2a1 1 0 000 2h6a1 1 0 100-2H7zm6 7a1 1 0 01-1 1H8a1 1 0 110-2h4a1 1 0 011 1zm-1 3a1 1 0 100 2H8a1 1 0 100-2h4z" clip-rule="evenodd"></path>
                            </svg>
                            Detail Legalisasi Laptop
                        </h1>
                        <p class="text-blue-100 flex items-center text-lg">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" clip-rule="evenodd"></path>
                            </svg>
                            Nomor Registrasi: <?= htmlspecialchars($data['id']) ?>
                        </p>
                    </div>
                    <div class="hidden md:block">
                        <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center pulse-animation">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Status Badge -->
            <div class="px-8 py-6">
                <?php 
                $status_config = [
                    'Baik' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-800', 'icon' => '✓'],
                    'Rusak' => ['bg' => 'bg-red-500', 'text' => 'text-red-800', 'icon' => '✗'],
                    'Perlu Perbaikan' => ['bg' => 'bg-amber-500', 'text' => 'text-amber-800', 'icon' => '⚠']
                ];
                $status = $data['status'];
                $config = $status_config[$status];
                ?>
                <div class="status-badge inline-flex items-center px-6 py-4 rounded-2xl">
                    <div class="w-4 h-4 <?= $config['bg'] ?> rounded-full mr-3 pulse-animation flex items-center justify-center text-white text-xs font-bold">
                        <?= $config['icon'] ?>
                    </div>
                    <span class="<?= $config['text'] ?> font-semibold text-lg">Status: <?= htmlspecialchars($status) ?></span>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="px-8 pb-8">
                <div class="grid lg:grid-cols-2 gap-8 mb-8">
                    <!-- Informasi Anggota -->
                    <div class="space-y-6">
                        <h2 class="text-2xl font-bold gradient-text flex items-center mb-6">
                            <svg class="w-7 h-7 mr-3 text-blue-600 icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                            Informasi Anggota
                        </h2>
                        
                        <div class="space-y-4">
                            <div class="info-card rounded-2xl p-6">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm-2 5V6a2 2 0 114 0v1H8z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-sm text-blue-600 font-semibold">Nama Lengkap</p>
                                </div>
                                <p class="text-xl font-bold text-gray-800"><?= htmlspecialchars($data['nama']) ?></p>
                            </div>
                            
                            <div class="info-card rounded-2xl p-6">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                    </svg>
                                    <p class="text-sm text-blue-600 font-semibold">Email</p>
                                </div>
                                <p class="text-xl font-bold text-gray-800"><?= htmlspecialchars($data['email']) ?></p>
                            </div>
                            
                            <div class="info-card rounded-2xl p-6">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                                    </svg>
                                    <p class="text-sm text-blue-600 font-semibold">Telepon</p>
                                </div>
                                <p class="text-xl font-bold text-gray-800"><?= htmlspecialchars($data['no_hp']) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detail Laptop -->
                    <div class="space-y-6">
                        <h2 class="text-2xl font-bold gradient-text flex items-center mb-6">
                            <svg class="w-7 h-7 mr-3 text-blue-600 icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"></path>
                            </svg>
                            Detail Laptop
                        </h2>
                        
                        <div class="space-y-4">
                            <div class="info-card rounded-2xl p-6">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-sm text-blue-600 font-semibold">Merk</p>
                                </div>
                                <p class="text-xl font-bold text-gray-800"><?= htmlspecialchars($data['merk']) ?></p>
                            </div>
                            
                            <div class="info-card rounded-2xl p-6">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-sm text-blue-600 font-semibold">Tipe</p>
                                </div>
                                <p class="text-xl font-bold text-gray-800"><?= htmlspecialchars($data['tipe']) ?></p>
                            </div>
                            
                            <div class="info-card rounded-2xl p-6">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-sm text-blue-600 font-semibold">Serial Number</p>
                                </div>
                                <p class="text-xl font-bold text-gray-800 font-mono bg-gray-100 px-3 py-1 rounded-lg"><?= htmlspecialchars($data['serial_number']) ?></p>
                            </div>
                            
                            <div class="info-card rounded-2xl p-6">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-sm text-blue-600 font-semibold">Tanggal Legalisasi</p>
                                </div>
                                <p class="text-xl font-bold text-gray-800"><?= date('d F Y', strtotime($data['created_at'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="section-divider"></div>
                
                <!-- Foto Bukti -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold gradient-text mb-6 flex items-center">
                        <svg class="w-7 h-7 mr-3 text-blue-600 icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                        </svg>
                        Foto Bukti
                    </h2>
                    <div class="image-container rounded-3xl p-8 flex justify-center">
                        <?php if ($data['file_bukti']): ?>
                            <div class="relative">
                                <img src="../uploads/legalisasi/<?= htmlspecialchars($data['file_bukti']) ?>" 
                                     alt="Foto bukti laptop <?= htmlspecialchars($data['merk']) ?> <?= htmlspecialchars($data['serial_number']) ?>"
                                     class="max-h-80 rounded-2xl shadow-2xl border-4 border-white/50">
                                <div class="absolute -top-2 -right-2 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center shadow-lg">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-gray-500 text-lg">Foto tidak tersedia</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="section-divider"></div>
                
                <!-- QR Code -->
                <div class="text-center">
                    <h2 class="text-2xl font-bold gradient-text mb-6 flex items-center justify-center">
                        <svg class="w-7 h-7 mr-3 text-blue-600 icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm2 2V5h1v1H5zM3 13a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3zm2 2v-1h1v1H5zM13 3a1 1 0 00-1 1v3a1 1 0 001 1h3a1 1 0 001-1V4a1 1 0 00-1-1h-3zm1 2v1h1V5h-1z" clip-rule="evenodd"></path>
                        </svg>
                        QR Code Verifikasi
                    </h2>
                    <div class="flex justify-center">
                        <div class="qr-container rounded-3xl p-8 inline-block">
                            <div id="qrcode" class="mb-4"></div>
                            <div class="flex items-center justify-center text-blue-600">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                                </svg>
                                <p class="font-semibold">Scan untuk memverifikasi keaslian dokumen</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Generate QR Code
        document.addEventListener('DOMContentLoaded', function() {
            const currentUrl = window.location.href;
            const qrContainer = document.getElementById('qrcode');
            
            // Clear any existing content
            qrContainer.innerHTML = '';
            
            // Create canvas for QR code
            const canvas = document.createElement('canvas');
            qrContainer.appendChild(canvas);
            
            QRCode.toCanvas(
                canvas, 
                currentUrl,
                { 
                    width: 200,
                    height: 200,
                    color: {
                        dark: '#1e3a8a',  // Dark blue
                        light: '#ffffff'  // White background
                    },
                    margin: 2
                },
                function(error) {
                    if (error) {
                        console.error('QR Code generation error:', error);
                        qrContainer.innerHTML = '<p class="text-red-500">Error generating QR code</p>';
                    }
                }
            );
        });
        
        // Add floating animation to background elements
        document.addEventListener('DOMContentLoaded', function() {
            const floatingElements = document.querySelectorAll('.floating-element');
            
            floatingElements.forEach((element, index) => {
                element.style.animationDelay = `${index * 2}s`;
            });
        });
        
        // Add smooth scroll behavior for better UX
        document.documentElement.style.scrollBehavior = 'smooth';
        
        // Add loading animation
        window.addEventListener('load', function() {
            const glassContainer = document.querySelector('.glass-container');
            glassContainer.style.opacity = '0';
            glassContainer.style.transform = 'translateY(20px)';
            glassContainer.style.transition = 'all 0.8s ease';
            
            setTimeout(() => {
                glassContainer.style.opacity = '1';
                glassContainer.style.transform = 'translateY(0)';
            }, 100);
        });
        
        // Add interactive hover effects
        document.addEventListener('DOMContentLoaded', function() {
            const infoCards = document.querySelectorAll('.info-card');
            
            infoCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.background = 'rgba(255, 255, 255, 0.9)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.background = 'rgba(255, 255, 255, 0.7)';
                });
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>