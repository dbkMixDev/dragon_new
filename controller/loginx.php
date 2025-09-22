<?php
// Durasi session 12 jam
$session_lifetime = 12 * 60 * 60; // 12 jam

session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path' => '/',
    'domain' => '', // kosong = domain saat ini
    'secure' => isset($_SERVER['HTTPS']), // hanya HTTPS jika tersedia
    'httponly' => true, // tidak bisa diakses JS
    'samesite' => 'Lax'
]);

ini_set('session.gc_maxlifetime', $session_lifetime);

session_start();
include '../include/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['namex'] ?? '');
    $password = $_POST['passx'] ?? '';
    $host = $_POST['user_info'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username dan Password wajib diisi.";
        header("Location: ../login.php");
        exit;
    }

    // Cek login attempts
    $stmt = $con->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE hostname = ?");
    $stmt->bind_param("s", $host);
    $stmt->execute();
    $stmt->store_result();

    $attempts = 0;
    $last_attempt = null;

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($attempts, $last_attempt);
        $stmt->fetch();
    }
    $stmt->close();

    if ($attempts >= 5) {
        $last = strtotime($last_attempt);
        $now = time();
        $diff = $now - $last;

        if ($diff < 120) {
            $_SESSION['error'] = "Terlalu banyak percobaan login. Silakan coba lagi setelah 2 menit.";
            header("Location: ../login.php");
            exit;
        } else {
            // Reset attempts
            $stmt = $con->prepare("UPDATE login_attempts SET attempts = 0 WHERE hostname = ?");
            $stmt->bind_param("s", $host);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Cek user
    $stmt = $con->prepare("SELECT id, username, email, pass, level, cabang, merchand FROM userx WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // User tidak ditemukan -> tambah login attempts
        // Proses tambah login attempts di bawah
        $user_found = false;
    } else {
        $stmt->bind_result($user_id, $user_username, $user_email, $user_pass, $user_level, $user_cabang, $user_merchand);
        $stmt->fetch();
        $user_found = true;
    }
    $stmt->close();

    if ($user_found && password_verify($password, $user_pass)) {
        // Login berhasil
        $_SESSION['user_id'] = $user_username;
        $_SESSION['username'] = $user_email;
        $_SESSION['level'] = $user_level;
        $_SESSION['cabang'] = $user_cabang;

        // Reset login attempts
        $stmt = $con->prepare("DELETE FROM login_attempts WHERE hostname = ?");
        $stmt->bind_param("s", $host);
        $stmt->execute();
        $stmt->close();

        // Update log & host
        $stmt = $con->prepare("UPDATE userx SET last_log = NOW(), host = ? WHERE id = ?");
        $stmt->bind_param("si", $host, $user_id);
        $stmt->execute();
        $stmt->close();

        // Generate and store token
        $tokenx = bin2hex(random_bytes(16));
        $stmt = $con->prepare("UPDATE userx SET login_token = ? WHERE username = ?");
        $stmt->bind_param("ss", $tokenx, $username);
        $stmt->execute();
        $stmt->close();

        $_SESSION['login_token'] = $tokenx;

        session_regenerate_id(true);

        if (is_null($user_merchand) || $user_merchand == '') {
            header("Location: ../merchant_setup.php");
        } else {
            header("Location: ../index.php");
        }
        exit;
    } else {
        // Login gagal, tambah login attempts
        if ($attempts > 0) {
            $stmt = $con->prepare("UPDATE login_attempts SET attempts = attempts + 1, last_attempt = NOW() WHERE hostname = ?");
            $stmt->bind_param("s", $host);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $con->prepare("INSERT INTO login_attempts (hostname, attempts, last_attempt) VALUES (?, 1, NOW())");
            $stmt->bind_param("s", $host);
            $stmt->execute();
            $stmt->close();
        }

        $_SESSION['error'] = "Username atau password salah.";
        header("Location: ../login.php");
        exit;
    }

} else {
    header("Location: ../login.php");
    exit;
}
