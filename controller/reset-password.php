<?php
session_start();
require_once '../include/config.php'; // koneksi database
require_once '../include/crypto.php'; // jika password ingin di-enkripsi, tapi gunakan password_hash lebih baik

// Ambil input
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm = trim($_POST['confirm'] ?? '');

// Validasi awal
if (!$email || !$password || !$confirm) {
    $_SESSION['error4'] = "Data tidak lengkap.";
    header("Location: ../reset-password.php?q=" . urlencode(encrypt($email)));
    exit;
}

if ($password !== $confirm) {
    $_SESSION['error4'] = "Password dan konfirmasi tidak cocok.";
    header("Location: ../reset-password.php?q=" . urlencode(encrypt($email)));
    exit;
}

// Cek apakah reset = 1
$stmt = $con->prepare("SELECT reset FROM recoverpw WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($resetStatus);
$stmt->fetch();
$stmt->close();

if ($resetStatus != 1) {
    $_SESSION['error4'] = "Token tidak valid atau belum diverifikasi.";
    header("Location: ../reset-password.php?q=" . urlencode(encrypt($email)));
    exit;
}

// Hash password baru
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Update password di tabel users (ganti sesuai struktur tabelmu)
$updateUser = $con->prepare("UPDATE userx SET pass = ? WHERE email = ?");
$updateUser->bind_param("ss", $hashedPassword, $email);
$updateUser->execute();
$updateUser->close();

// Tandai reset sebagai selesai (reset = 0)
$clearReset = $con->prepare("UPDATE recoverpw SET reset = 0 WHERE email = ?");
$clearReset->bind_param("s", $email);
$clearReset->execute();
$clearReset->close();

$_SESSION['success_redirect'] = true;
$_SESSION['success4'] = "Password berhasil diubah. Silakan login.";
header("Location: ../reset-password.php?q=" . urlencode(encrypt($email)));
exit;
