<?php
$profile_name = "Admin "; // Default
$profile_photo = "default_profile.jpg"; // Default
$id_user = $_SESSION['user_id']; // Make sure to get the user ID from the session

$stmt_profile = $conn->prepare('SELECT nama_lengkap, foto FROM profile WHERE id_user = ?');
if ($stmt_profile) {
    $stmt_profile->bind_param('i', $id_user);
    $stmt_profile->execute();
    $stmt_profile->bind_result($fetched_name, $fetched_photo);
    if ($stmt_profile->fetch()) {
        $profile_name = htmlspecialchars($fetched_name);
        $profile_photo = htmlspecialchars($fetched_photo);
    }
    $stmt_profile->close();
} else {
    // Handle error if the statement could not be prepared
    $message = 'Error preparing statement for profile fetch.';
    $message_type = 'error';
}
?>


