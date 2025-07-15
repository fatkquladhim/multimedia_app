<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blue Login Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .custom-blue-300 {
            --tw-gradient-to: #0074f5 var(--tw-gradient-to-position);
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
        
        .profile-circle {
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .checkbox-custom {
            appearance: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(147, 197, 253, 0.6);
            border-radius: 3px;
            background: rgba(59, 130, 246, 0.1);
            position: relative;
            cursor: pointer;
        }
        
        .checkbox-custom:checked {
            background: rgba(59, 130, 246, 0.3);
            border-color: rgba(147, 197, 253, 0.8);
        }
        
        .checkbox-custom:checked::after {
            content: '✓';
            position: absolute;
            top: -2px;
            left: 1px;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-600 via-blue-500 to-blue-300 flex items-center justify-center p-4">
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
            $error = 'User tidak ditemukan!';
        }
        $stmt->close();
        $conn->close();
    }
    ?>
    <div class="w-full max-w-md">
        <!-- Profile Picture -->
        <div class="flex justify-center mb-8">
            <div>
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <img src="./mm-removebg-preview.png" style="max-width:120px;">
                </svg>
            </div>
        </div>

        <!-- Login Form -->
        <form method="post" class="space-y-6">
            <!-- Error Message Display -->
            <?php if ($error): ?>
                <p class="text-red-300 text-center"><?php echo $error; ?></p>
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
                    id="username"
                    name="username" <!-- Added name attribute -->
                    placeholder="Username"
                    class="glass-input w-full pl-12 pr-4 py-4 rounded-full text-white placeholder-blue-100 focus:outline-none transition-all duration-200"
                    required
                />
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
                    id="password"
                    name="password" <!-- Added name attribute -->
                    placeholder="Password"
                    class="glass-input w-full pl-12 pr-4 py-4 rounded-full text-white placeholder-blue-100 focus:outline-none transition-all duration-200"
                    required
                />
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember" <!-- Added name attribute for remember me -->
                        class="checkbox-custom"
                    />
                    <span class="text-blue-100">Remember Me</span>
                </label>
                <a href="#" class="text-blue-100 hover:text-white transition-colors duration-200">
                    Forgot Password?
                </a>
            </div>

            <!-- Login Button -->
            <button
                type="submit" <!-- Changed type to submit -->
                class="login-btn w-full py-4 text-blue-600 font-semibold rounded-full focus:outline-none"
            >
                LOGIN
            </button>
        </form>

        <!-- Footer -->
    </div>

    <script>
        // The JavaScript handleLogin() function is no longer needed for form submission
        // as the form will now submit directly to the PHP script.
        // However, the focus effects can remain.
        
        // Add enter key support (optional, as form submission handles this)
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                // If you want to trigger form submission on Enter, you can do:
                // document.querySelector('form').submit();
                // Or remove this if the default form submission is sufficient.
            }
        });
        
        // Add focus effects
        const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('scale-105');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('scale-105');
            });
        });
    </script>
</body>
</html>
