<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}
require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Hapus anggota
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM anggota WHERE id = $id");
    echo '<p style="color:red">Anggota berhasil dihapus.</p>';
}

// Proses tambah/edit anggota
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $nim = $_POST['nim'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $email = $_POST['email'] ?? '';
    $no_hp = $_POST['no_hp'] ?? '';
    if (isset($_POST['id']) && $_POST['id']) {
        // Edit
        $id = $_POST['id'];
        $stmt = $conn->prepare('UPDATE anggota SET nama=?, nim=?, alamat=?, email=?, no_hp=? WHERE id=?');
        $stmt->bind_param('sssssi', $nama, $nim, $alamat, $email, $no_hp, $id);
        $stmt->execute();
        $stmt->close();
        echo '<p style="color:green">Data anggota berhasil diupdate.</p>';
    } else {
        // Tambah
        $stmt = $conn->prepare('INSERT INTO anggota (nama, nim, alamat, email, no_hp) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $nama, $nim, $alamat, $email, $no_hp);
        $stmt->execute();
        $stmt->close();
        echo '<p style="color:green">Anggota baru berhasil ditambahkan.</p>';
    }
}

// Ambil data anggota untuk edit
$edit_data = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM anggota WHERE id = $id");
    $edit_data = $result->fetch_assoc();
}

// Tampilkan form tambah/edit
?>
<h2><?php echo $edit_data ? 'Edit Anggota' : 'Tambah Anggota'; ?></h2>
<form method="post">
    <?php if ($edit_data) echo '<input type="hidden" name="id" value="'.$edit_data['id'].'">'; ?>
    <input type="text" name="nama" placeholder="Nama" value="<?php echo $edit_data['nama'] ?? ''; ?>" required><br>
    <input type="text" name="nim" placeholder="NIM" value="<?php echo $edit_data['nim'] ?? ''; ?>" required><br>
    <input type="text" name="alamat" placeholder="Alamat" value="<?php echo $edit_data['alamat'] ?? ''; ?>"><br>
    <input type="email" name="email" placeholder="Email" value="<?php echo $edit_data['email'] ?? ''; ?>"><br>
    <input type="text" name="no_hp" placeholder="No HP" value="<?php echo $edit_data['no_hp'] ?? ''; ?>"><br>
    <button type="submit">Simpan</button>
</form>

<h2>Daftar Anggota</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>Nama</th>
        <th>NIM</th>
        <th>Alamat</th>
        <th>Email</th>
        <th>No HP</th>
        <th>Aksi</th>
    </tr>
    <?php
    $result = $conn->query('SELECT * FROM anggota ORDER BY id DESC');
    while ($row = $result->fetch_assoc()) {
    ?>
    <tr>
        <td><?php echo htmlspecialchars($row['nama']); ?></td>
        <td><?php echo htmlspecialchars($row['nim']); ?></td>
        <td><?php echo htmlspecialchars($row['alamat']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
        <td>
            <a href="anggota-edit.php?edit=<?php echo $row['id']; ?>">Edit</a> |
            <a href="anggota-edit.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin hapus anggota?')">Hapus</a>
        </td>
    </tr>
    <?php } ?>
</table>
<?php $conn->close(); ?>
