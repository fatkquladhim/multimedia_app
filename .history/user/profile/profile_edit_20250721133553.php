<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$id_user = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'create';

$nama_lengkap = '';
$email = '';
$alamat = '';
$no_hp = '';
$current_foto = '';

if ($action === 'edit') {
    $stmt = $conn->prepare('SELECT nama_lengkap, email, alamat, no_hp, foto FROM profile WHERE id_user = ?');
    $stmt->bind_param('i', $id_user);
    $stmt->execute();
    $stmt->bind_result($nama_lengkap, $email, $alamat, $no_hp, $current_foto);
    $stmt->fetch();
    $stmt->close();
}

include '../header_beckend.php';
include '../header.php';
?>

<style> 
    
    .profile-header {
        display: flex;
        align-items: center;
        padding: 1rem 1.5rem;
        color: white;
        position: relative;
    }
    
    .back-btn {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0.5rem;
        margin-right: 1rem;
        border-radius: 50%;
        transition: background-color 0.2s;
    }
    
    .back-btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .profile-title {
        font-size: 1.125rem;
        font-weight: 600;
        flex: 1;
        text-align: center;
        margin-right: 3rem;
    }
    
    .profile-form {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border-radius: 0;
        padding: 2rem 1.5rem;
        margin-top: 1rem;
        min-height: calc(100vh - 120px);
    }
    
    .avatar-section {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .avatar-container {
        position: relative;
        display: inline-block;
        margin-bottom: 1rem;
    }
    
    .avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
    }
    
    .avatar-edit {
        position: absolute;
        bottom: 0;
        right: 0;
        background: #4F46E5;
        border: 3px solid white;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: white;
        font-size: 0.875rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        color: white;
        font-size: 1rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .form-input {
        width: 100%;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 1rem;
        color: white;
        font-size: 1rem;
        transition: all 0.2s;
        box-sizing: border-box;
    }
    
    .form-input::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }
    
    .form-input:focus {
        outline: none;
        border-color: #4F46E5;
        background: rgba(255, 255, 255, 0.15);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 1rem center;
        background-repeat: no-repeat;
        background-size: 1rem;
    }
    
    .file-input-wrapper {
        position: relative;
        display: none;
    }
    
    .save-button {
        width: 100%;
        background: #4F46E5;
        color: white;
        border: none;
        border-radius: 12px;
        padding: 1rem;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        margin-top: 2rem;
        transition: background-color 0.2s;
    }
    
    .save-button:hover {
        background: #4338CA;
    }
    
    .cancel-button {
        width: 100%;
        background: transparent;
        color: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 12px;
        padding: 1rem;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        margin-top: 1rem;
        text-decoration: none;
        display: block;
        text-align: center;
        transition: all 0.2s;
    }
    
    .cancel-button:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    /* Desktop Responsive */
    @media (min-width: 768px) {
        .profile-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .profile-wrapper {
            max-width: 480px;
            width: 100%;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .profile-form {
            background: white;
            color: #374151;
            padding: 2rem;
            min-height: auto;
        }
        
        .form-label {
            color: #374151;
        }
        
        .form-input {
            background: #F9FAFB;
            border: 1px solid #D1D5DB;
            color: #374151;
        }
        
        .form-input::placeholder {
            color: #9CA3AF;
        }
        
        .form-input:focus {
            background: white;
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .avatar {
            border: 4px solid #E5E7EB;
            background: #F3F4F6;
            color: #9CA3AF;
        }
        
        .cancel-button {
            color: #6B7280;
            border-color: #D1D5DB;
        }
        
        .cancel-button:hover {
            background: #F9FAFB;
            color: #374151;
        }
    }
</style>

<div class="profile-container">
    <div class="profile-wrapper">
        <!-- Header -->
        <div class="profile-header">
            <button class="back-btn" onclick="window.location.href='profile_view.php'">
                ←
            </button>
            <h1 class="profile-title"><?php echo ($action === 'edit' ? 'Edit Profile' : 'Create Profile'); ?></h1>
        </div>

        <!-- Form -->
        <div class="profile-form">
            <form method="post" action="profile_store.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $action; ?>">

                <!-- Avatar Section -->
                <div class="avatar-section">
                    <div class="avatar-container">
                        <?php if ($current_foto): ?>
                            <img src="../../uploads/profiles/<?php echo htmlspecialchars($current_foto); ?>" alt="Profile" class="avatar" id="avatarImg">
                        <?php else: ?>
                            <div class="avatar" id="avatarImg">👤</div>
                        <?php endif; ?>
                        <label for="foto" class="avatar-edit">📷</label>
                    </div>
                    <input type="file" id="foto" name="foto" accept="image/*" class="file-input-wrapper" onchange="previewImage(this)">
                </div>

                <!-- Name Field -->
                <div class="form-group">
                    <label for="nama_lengkap" class="form-label">Name</label>
                    <input type="text" 
                           id="nama_lengkap" 
                           name="nama_lengkap" 
                           placeholder="Enter your full name" 
                           value="<?php echo htmlspecialchars($nama_lengkap); ?>" 
                           class="form-input" 
                           required>
                </div>

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           placeholder="Enter your email address" 
                           value="<?php echo htmlspecialchars($email); ?>" 
                           class="form-input" 
                           required>
                </div>

                <!-- Address Field -->
                <div class="form-group">
                    <label for="alamat" class="form-label">Address</label>
                    <input type="text" 
                           id="alamat" 
                           name="alamat" 
                           placeholder="Enter your address" 
                           value="<?php echo htmlspecialchars($alamat); ?>" 
                           class="form-input">
                </div>

                <!-- Phone Number Field -->
                <div class="form-group">
                    <label for="no_hp" class="form-label">Phone Number</label>
                    <input type="text" 
                           id="no_hp" 
                           name="no_hp" 
                           placeholder="Enter your phone number" 
                           value="<?php echo htmlspecialchars($no_hp); ?>" 
                           class="form-input">
                </div>

                <!-- Action Buttons -->
                <button type="submit" class="save-button">
                    <?php echo ($action === 'edit' ? 'Save changes' : 'Create Profile'); ?>
                </button>
                
                <a href="profile_view.php" class="cancel-button">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const avatarImg = document.getElementById('avatarImg');
            if (avatarImg.tagName === 'IMG') {
                avatarImg.src = e.target.result;
            } else {
                const newImg = document.createElement('img');
                newImg.src = e.target.result;
                newImg.className = 'avatar';
                newImg.id = 'avatarImg';
                avatarImg.parentNode.replaceChild(newImg, avatarImg);
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php
include '../footer.php';
$conn->close();
?>