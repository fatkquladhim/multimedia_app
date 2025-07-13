<?php
require_once '../includes/db_config.php';
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $error = 'Username sudah terdaftar!';
    } else {
        $stmt->close();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $username, $hash, $role);
        if ($stmt->execute()) {
            $success = 'Registrasi berhasil! Silakan login.';
        } else {
            $error = 'Gagal registrasi!';
        }
    }
    $stmt->close();
    $conn->close();
}
?>
<form method="post">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <select name="role">
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select><br>
    <button type="submit">Register</button>
</form>
<?php if ($error) echo '<p style="color:red">'.$error.'</p>'; ?>
<?php if ($success) echo '<p style="color:green">'.$success.'</p>'; ?>
