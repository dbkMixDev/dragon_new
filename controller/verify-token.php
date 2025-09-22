<?php
session_start();
 
require '../include/config.php'; // Koneksi ke database

// Ambil input dan sanitasi
$email = trim($_POST['email'] ?? '');
$token = trim($_POST['token'] ?? '');
$email2 = trim($_POST['email2'] ?? '');

// Validasi awal
if (!$email || !$token || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error3'] = "Permintaan tidak valid.";
    header("Location: ../auth-token.php?q=" . urlencode($email2));
    exit;
}

// Ambil data token dari DB
$stmt = $con->prepare("SELECT token, `update` FROM recoverpw WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($storedToken, $updatedAt);
$stmt->fetch();
$stmt->close();

if (!$storedToken || !$updatedAt) {
    $_SESSION['error3'] = "Email tidak ditemukan atau belum meminta verifikasi.";
    header("Location: ../auth-token.php?q=" . urlencode($email2));
    exit;
}

// Validasi token
if ($storedToken !== $token) {
    $_SESSION['error3'] = "Kode token verifikasi anda salah.";
    header("Location: ../auth-token.php?q=" . urlencode($email2));
    exit;
}

// Validasi waktu
$updatedTimestamp = strtotime($updatedAt);
if (!$updatedTimestamp) {
    $_SESSION['error3'] = "Format waktu tidak valid. Hubungi admin.";
    header("Location: ../auth-token.php?q=" . urlencode($email2));
    exit;
}

// Cek kedaluwarsa token (5 menit)
$expirySeconds = 5 * 60;
if ((time() - $updatedTimestamp) > $expirySeconds) {
    $_SESSION['error3'] = "Token sudah kedaluwarsa. Silakan minta ulang.";
    header("Location: ../auth-token.php?q=" . urlencode($email2));
    exit;
}

// ✅ Token valid - Update field 'reset' menjadi 1
$updateStmt = $con->prepare("UPDATE recoverpw SET reset = 1 WHERE email = ?");
$updateStmt->bind_param("s", $email);
$updateStmt->execute();
$updateStmt->close();

// Redirect ke halaman reset password
header("Location: ../reset-password.php?q=" . urlencode($email2));
exit;
