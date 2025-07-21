<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$stmt = $conn->prepare('SELECT nama_lengkap, email, alamat, no_hp, foto FROM profile WHERE id_user = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($nama_lengkap, $email, $alamat, $no_hp, $foto);
$stmt->fetch();
$stmt->close();
$profile_exists = !empty($nama_lengkap);

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

    .profile-content {
        background: white;
        border-radius: 24px 24px 0 0;
        padding: 2rem 1.5rem;
        margin-top: 1rem;
        min-height: calc(100vh - 120px);
        position: relative;
    }

    .avatar-section {
        text-align: center;
        margin-bottom: 2rem;
        margin-top: -4rem;
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
        border: 6px solid white;
        background: #F3F4F6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9CA3AF;
        font-size: 2rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .profile-info {
        margin-top: 1rem;
    }

    .info-item {
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #E5E7EB;
        padding-bottom: 1rem;
    }

    .info-item:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
    }

    .info-label {
        color: #6B7280;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .info-value {
        color: #111827;
        font-size: 1rem;
        font-weight: 500;
    }

    .info-value.empty {
        color: #9CA3AF;
        font-style: italic;
    }

    .action-buttons {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #E5E7EB;
    }

    .action-button {
        width: 100%;
        padding: 1rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        text-decoration: none;
        display: block;
        text-align: center;
        transition: all 0.2s;
        margin-bottom: 1rem;
        cursor: pointer;
    }

    .btn-primary {
        background: #4F46E5;
        color: white;
        border: none;
    }

    .btn-primary:hover {
        background: #4338CA;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
    }

    .btn-success {
        background: #10B981;
        color: white;
        border: none;
    }

    .btn-success:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .message {
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }

    .message.success {
        background: #D1FAE5;
        color: #065F46;
        border: 1px solid #A7F3D0;
    }

    .message.error {
        background: #FEE2E2;
        color: #991B1B;
        border: 1px solid #FECACA;
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

        .profile-content {
            background: white;
            border-radius: 0;
            margin-top: 0;
            min-height: auto;
        }

        .avatar-section {
            margin-top: -4rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        .action-button {
            width: auto;
            flex: 1;
            margin-bottom: 0;
        }
    }
</style>

<div class="profile-container">
    <div class="profile-wrapper">
        <!-- Header -->
        <div class="profile-header">
            <div>
            </div>
            <h1 class="profile-title">Profile</h1>
        </div>

        <!-- Content -->
        <div class="profile-content">
            <!-- Message Display -->
            <?php if (isset($_GET['status'])): ?>
                <div class="message <?php echo htmlspecialchars($_GET['status']); ?>">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>

            <!-- Avatar Section -->
            <div class="avatar-section">
                <div class="avatar-container">
                    <?php if ($foto): ?>
                        <img src="../../uploads/profiles/<?php echo htmlspecialchars($foto); ?>" alt="Profile" class="avatar">
                    <?php else: ?>
                        <div class="avatar">👤</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="profile-info">
                <div class="info-item">
                    <span class="info-label">Username</span>
                    <div class="info-value"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                </div>

                <div class="info-item">
                    <span class="info-label">Nama Lengkap</span>
                    <div class="info-value <?php echo empty($nama_lengkap) ? 'empty' : ''; ?>">
                        <?php echo htmlspecialchars($nama_lengkap ?? 'Belum diisi'); ?>
                    </div>
                </div>

                <div class="info-item">
                    <span class="info-label">Email</span>
                    <div class="info-value <?php echo empty($email) ? 'empty' : ''; ?>">
                        <?php echo htmlspecialchars($email ?? 'Belum diisi'); ?>
                    </div>
                </div>

                <div class="info-item">
                    <span class="info-label">Alamat</span>
                    <div class="info-value <?php echo empty($alamat) ? 'empty' : ''; ?>">
                        <?php echo htmlspecialchars($alamat ?? 'Belum diisi'); ?>
                    </div>
                </div>

                <div class="info-item">
                    <span class="info-label">No HP</span>
                    <div class="info-value <?php echo empty($no_hp) ? 'empty' : ''; ?>">
                        <?php echo htmlspecialchars($no_hp ?? 'Belum diisi'); ?>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <?php if ($profile_exists): ?>
                    <a href="profile_edit.php?action=edit" class="action-button btn-primary">Edit Profile</a>
                <?php else: ?>
                    <a href="profile_create.php?action=create" class="action-button btn-success">Create Profile</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
include '../footer.php';
$conn->close();
?>