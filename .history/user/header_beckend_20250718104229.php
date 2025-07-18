<?php
$profile_name = "User"; // Default
$profile_photo = "../public/assets/imgs/profile_umum.jpg"; // Default
$stmt_profile = $conn->prepare('SELECT nama_lengkap, foto FROM profile WHERE id_user = ?');
$stmt_profile->bind_param('i', $id_user);
$stmt_profile->execute();
$stmt_profile->bind_result($fetched_name, $fetched_photo);
if ($stmt_profile->fetch()) {
    $profile_name = htmlspecialchars($fetched_name);
    $profile_photo = htmlspecialchars($fetched_photo);
}
$stmt_profile->close();
?>
