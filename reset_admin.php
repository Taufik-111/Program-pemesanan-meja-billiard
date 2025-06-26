<?php
include 'config/db.php';

$email = 'admin@billiard.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = :password WHERE email = :email");
$stmt->bindParam(':password', $password);
$stmt->bindParam(':email', $email);

if ($stmt->execute()) {
    echo "Password berhasil direset ke: admin123";
} else {
    echo "Gagal reset password";
}
?>
