<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$id_user = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'update_profile') {
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $email = $_POST['email'] ?? '';

    // Start transaction for atomicity
    $conn->begin_transaction();
    $success = true;

    // 1. Update profile table
    $stmt_profile = $conn->prepare('UPDATE users SET nama_lengkap = ?, email = ? WHERE id_user = ?');
    if ($stmt_profile) {
        $stmt_profile->bind_param('ssi', $nama_lengkap, $email, $id_user);
        if (!$stmt_profile->execute()) {
            $success = false;
            error_log("Error updating profile table: " . $stmt_profile->error);
        }
        $stmt_profile->close();
    } else {
        $success = false;
        error_log("Error preparing profile update statement: " . $conn->error);
    }


    // 2. Update users table (assuming 'users' table also has 'nama_lengkap' and 'email' columns)
    // If your 'users' table only has 'username' and 'password', you might not need this part for 'nama_lengkap' and 'email'.
    // If 'email' is used as username, you might want to update 'username' column in 'users' table as well.
    $stmt_users = $conn->prepare('UPDATE users SET email = ?, nama_lengkap = ? WHERE id = ?'); // Adjust column names as per your 'users' table
    if ($stmt_users) {
        $stmt_users->bind_param('ssi', $email, $nama_lengkap, $id_user);
        if (!$stmt_users->execute()) {
            $success = false;
            error_log("Error updating users table: " . $stmt_users->error);
        }
        $stmt_users->close();
    } else {
        $success = false;
        error_log("Error preparing users update statement: " . $conn->error);
    }


    if ($success) {
        $conn->commit();
        header('Location: profile_settings.php?status=success&message=Profil berhasil diperbarui!');
    } else {
        $conn->rollback();
        header('Location: profile_settings.php?status=error&message=Gagal memperbarui profil. Terjadi kesalahan database.');
    }

} elseif ($action === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Fetch current hashed password from users table
    $stmt_user = $conn->prepare('SELECT password FROM users WHERE id = ?');
    $stmt_user->bind_param('i', $id_user);
    $stmt_user->execute();
    $stmt_user->bind_result($hashed_password);
    $stmt_user->fetch();
    $stmt_user->close();

    if (!password_verify($current_password, $hashed_password)) {
        header('Location: profile_settings.php?status=error&message=Sandi saat ini salah.');
        $conn->close();
        exit;
    }

    if ($new_password !== $confirm_password) {
        header('Location: profile_settings.php?status=error&message=Konfirmasi sandi baru tidak cocok.');
        $conn->close();
        exit;
    }

    // Hash the new password
    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password in users table
    $stmt_update_password = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
    if ($stmt_update_password) {
        $stmt_update_password->bind_param('si', $new_hashed_password, $id_user);
        if ($stmt_update_password->execute()) {
            header('Location: profile_settings.php?status=success&message=Sandi berhasil diubah!');
        } else {
            error_log("Error updating password: " . $stmt_update_password->error);
            header('Location: profile_settings.php?status=error&message=Gagal mengubah sandi.');
        }
        $stmt_update_password->close();
    } else {
        error_log("Error preparing password update statement: " . $conn->error);
        header('Location: profile_settings.php?status=error&message=Gagal mengubah sandi.');
    }

} else {
    header('Location: profile_settings.php?status=error&message=Aksi tidak valid.');
}

$conn->close();
 include './header_beckend.php'; 
exit;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pengaturan Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 0.375rem;
            box-sizing: border-box;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-primary { background-color: #4F46E5; color: white; border: none; }
        .btn-primary:hover { background-color: #4338CA; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .sidebar {
            transition: width 0.3s ease-in-out;
        }

        .sidebar-text {
            transition: opacity 0.3s ease-in-out, margin-left 0.3s ease-in-out;
        }

        .sidebar-nav-item {
            justify-content: flex-start;
        }

        .sidebar.collapsed .sidebar-nav-item {
            justify-content: center;
        }

        .sidebar.collapsed .sidebar-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
        }

        .sidebar.collapsed .sidebar-logo-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
        }

        .sidebar.collapsed .sidebar-logo-icon {
            margin-right: 0 !important;
        }

        .sidebar.collapsed .sidebar-create-button .sidebar-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
        }

        .sidebar.collapsed .sidebar-create-button i {
            margin-right: 0 !important;
        }

        .sidebar.collapsed .sidebar-upgrade-section {
            opacity: 0;
            height: 0;
            overflow: hidden;
            padding-top: 0;
            padding-bottom: 0;
            margin-top: 0;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden ">
        <div class="flex h-screen">
            <?php include '../sidebar.php'; ?>

            <div class="flex-1 p-2">
                <?php include './header_frontend.php'; ?>

                <main class="p-6">
                    <?php if ($message): ?>
                        <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <div class="bg-white p-6 rounded-lg shadow-md max-w-md mx-auto">
                        <h2 class="text-xl font-bold mb-4">Pengaturan Akun</h2>

                        <!-- Form untuk mengubah Nama Lengkap dan Email -->
                        <form method="post" action="profile_update.php">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="form-group">
                                <label for="nama_lengkap">Nama Lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo $nama_lengkap; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
                            </div>
                            <div class="flex space-x-4 mt-6">
                                <button type="submit" class="btn btn-primary">Update Profil</button>
                            </div>
                        </form>

                        <hr class="my-8">

                        <!-- Form untuk mengubah Sandi -->
                        <form method="post" action="profile_update.php">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-group">
                                <label for="current_password">Sandi Saat Ini</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Sandi Baru</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Konfirmasi Sandi Baru</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="flex space-x-4 mt-6">
                                <button type="submit" class="btn btn-primary">Ubah Sandi</button>
                            </div>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </div>
    <script>
        // Sidebar toggle logic (same as dashboard.php)
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');
        const sidebarLogoText = document.querySelector('.sidebar-logo-text');
        const sidebarLogoIcon = document.querySelector('.sidebar-logo-icon');
        const sidebarNavItems = document.querySelectorAll('.sidebar-nav-item');
        const sidebarCreateButton = document.querySelector('.sidebar-create-button');
        const sidebarUpgradeSection = document.querySelector('.sidebar-upgrade-section');

        let isSidebarOpen = true;

        sidebarToggle.addEventListener('click', () => {
            if (isSidebarOpen) {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20', 'collapsed');

                sidebarTexts.forEach(text => { text.classList.add('opacity-0', 'pointer-events-none'); });
                if (sidebarLogoText) sidebarLogoText.classList.add('opacity-0', 'pointer-events-none');
                if (sidebarUpgradeSection) sidebarUpgradeSection.classList.add('opacity-0', 'h-0', 'p-0', 'mt-0', 'pointer-events-none');

                if (sidebarLogoIcon) {
                    sidebarLogoIcon.classList.remove('space-x-2');
                    sidebarLogoIcon.classList.add('mx-auto');
                }
                sidebarNavItems.forEach(item => {
                    item.classList.remove('space-x-3', 'px-4');
                    item.classList.add('justify-center', 'px-0');
                });
                if (sidebarCreateButton) {
                    sidebarCreateButton.classList.remove('space-x-2');
                    sidebarCreateButton.classList.add('justify-center');
                    if (sidebarCreateButton.querySelector('button')) {
                        sidebarCreateButton.querySelector('button').classList.remove('space-x-2');
                        sidebarCreateButton.querySelector('button').classList.add('justify-center');
                    }
                }

                sidebarToggle.querySelector('i').classList.replace('fa-bars', 'fa-arrow-right');

            } else {
                sidebar.classList.remove('w-20', 'collapsed');
                sidebar.classList.add('w-64');

                sidebarTexts.forEach(text => { text.classList.remove('opacity-0', 'pointer-events-none'); });
                if (sidebarLogoText) sidebarLogoText.classList.remove('opacity-0', 'pointer-events-none');
                if (sidebarUpgradeSection) sidebarUpgradeSection.classList.remove('opacity-0', 'h-0', 'p-0', 'mt-0', 'pointer-events-none');

                if (sidebarLogoIcon) {
                    sidebarLogoIcon.classList.remove('mx-auto');
                    sidebarLogoIcon.classList.add('space-x-2');
                }
                sidebarNavItems.forEach(item => {
                    item.classList.remove('justify-center', 'px-0');
                    item.classList.add('space-x-3', 'px-4');
                });
                if (sidebarCreateButton) {
                    sidebarCreateButton.classList.remove('justify-center');
                    sidebarCreateButton.classList.add('space-x-2');
                    if (sidebarCreateButton.querySelector('button')) {
                        sidebarCreateButton.querySelector('button').classList.remove('justify-center');
                        sidebarCreateButton.querySelector('button').classList.add('space-x-2');
                    }
                }

                sidebarToggle.querySelector('i').classList.replace('fa-arrow-right', 'fa-bars');
            }
            isSidebarOpen = !isSidebarOpen;
        });
    </script>
</body>
</html>
