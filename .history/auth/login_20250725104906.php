<?php
session_start();
require_once '../includes/db_config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    // Query user
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $stmt = $conn->prepare('SELECT id, username, password, role FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            if ($row['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../user/dashboard.php');
            }
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'User  tidak ditemukan!';
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
    <title>Blue Login Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
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

            0%,
            100% {
                transform: translateY(0px) scale(1);
                opacity: 0.3;
            }

            50% {
                transform: translateY(-20px) scale(1.05);
                opacity: 0.6;
            }
        }

        .glass-input {
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(147, 197, 253, 0.3);
            backdrop-filter: blur(10px);
        }

        .glass-input:focus {
            background: rgba(59, 130, 246, 0.3);
            border-color: rgba(147, 197, 253, 0.5);
            box-shadow: 0 0 0 2px rgba(147, 197, 253, 0.3);
        }

        .login-btn {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
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
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-600 via-blue-500 to-blue-300 flex items-center justify-center p-4">
    <div class="floating-element float-1"></div>
    <div class="floating-element float-2"></div>
    <div class="floating-element float-3"></div>

    <div class="w-full max-w-md">
        <!-- Profile Picture -->
        <div class="flex justify-center mb-8">
            <div>
                <img src="../public/assets/imgs/rev-removebg-preview.png" style="max-width:80px;">
            </div>
        </div>

        <!-- Login Form -->
        <form method="post" class="space-y-6">
            <!-- Error Message -->
            <?php if ($error): ?>
                <p class="text-red-500 text-center"><?php echo $error; ?></p>
            <?php endif; ?>

            <!-- Username Field -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <input
                    type="text"
                    name="username"
                    id="username"
                    placeholder="Username"
                    class="glass-input w-full pl-12 pr-4 py-4 rounded-full text-white placeholder-blue-100 focus:outline-none transition-all duration-200"
                    required />
            </div>

            <!-- Password Field -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Password"
                    class="glass-input w-full pl-12 pr-4 py-4 rounded-full text-white placeholder-blue-100 focus:outline-none transition-all duration-200"
                    required />
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input
                        type="checkbox"
                        id="remember"
                        class="checkbox-custom" />
                    <span class="text-blue-100">Remember Me</span>
                </label>
                <a href="#" class="text-blue-100 hover:text-white transition-colors duration-200">
                    Forgot Password?
                </a>
            </div>

            <!-- Login Button -->
                <button
                    type="submit"
                    class="login-btn w-full py-2 font-semibold rounded-full focus:outline-none glass-header px-6 py-4 text-white relative z-10">
                    LOGIN
                </button>
        </form>
    </div>
</body>

</html>