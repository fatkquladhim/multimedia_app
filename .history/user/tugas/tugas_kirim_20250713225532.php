<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_tugas = $_POST['id_tugas'] ?? null;
    $jawaban = $_POST['jawaban'] ?? '';

    if (!$id_tugas || empty($jawaban)) {
        header('Location: tugas_user.php?status=error&message=Data tugas tidak valid');
        exit;
    }

    // Handle file upload
    $file_jawaban = '';
    if (isset($_FILES['file_jawaban']) && $_FILES['file_jawaban']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['file_jawaban']['type'], $allowed_types)) {
            header('Location: tugas_user.php?status=error&message=Tipe file tidak diizinkan');
            exit;
        }

        if ($_FILES['file_jawaban']['size'] > $max_size) {
            header('Location: tugas_user.php?status=error&message=Ukuran file terlalu besar');
            exit;
        }

        $file_jawaban = 'uploads/tugas_jawaban/' . basename($_FILES['file_jawaban']['name']);
        if (!move_uploaded_file($_FILES['file_jawaban']['tmp_name'], '../../' . $file_jawaban)) {
            header('Location: tugas_user.php?status=error&message=Gagal mengunggah file');
            exit;
        }
    }

    $stmt = $conn->prepare('INSERT INTO tugas_jawaban (id_tugas, id_user, jawaban, file_jawaban, tanggal_kirim) VALUES (?, ?, ?, ?, NOW())');
    $stmt->bind_param('iiss', $id_tugas, $_SESSION['user_id'], $jawaban, $file_jawaban);

    if ($stmt->execute()) {
        header('Location: tugas_user.php?status=success&message=Tugas berhasil dikirim');
    } else {
        header('Location: tugas_user.php?status=error&message=Gagal mengirim tugas');
    }
    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id_tugas = $_GET['id'] ?? null;
    if (!$id_tugas) {
        header('Location: tugas_user.php?status=error&message=ID tugas tidak valid');
        exit;
    }

    $stmt = $conn->prepare('SELECT judul, deskripsi, deadline FROM tugas WHERE id = ? AND id_penerima_tugas = ?');
    $stmt->bind_param('ii', $id_tugas, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $tugas = $result->fetch_assoc();

    if (!$tugas) {
        header('Location: tugas_user.php?status=error&message=Tugas tidak ditemukan');
        exit;
    }

    $stmt->close();
    ?>
    <h2>Kerjakan Tugas</h2>
    <p><strong>Judul:</strong> <?php echo htmlspecialchars($tugas['judul']); ?></p>
    <p><strong>Deskripsi:</strong> <?php echo htmlspecialchars($tugas['deskripsi']); ?></p>
    <p><strong>Deadline:</strong> <?php echo htmlspecialchars($tugas['deadline']); ?></p>
    <form method="post" action="tugas_kirim.php" enctype="multipart/form-data">
        <input type="hidden" name="id_tugas" value="<?php echo $id_tugas; ?>">
        <textarea name="jawaban" placeholder="Jawaban Anda" required></textarea><br>
        <input type="file" name="file_jawaban" accept="application/pdf,image/jpeg,image/png"><br>
        <button type="submit">Kirim Jawaban</button>
    </form>
    <p><a href="tugas_user.php">Kembali ke Tugas Saya</a></p>
    <?php
}

$conn->close();
?>
