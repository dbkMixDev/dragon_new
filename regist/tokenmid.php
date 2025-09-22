<?php
// ENABLE ERROR REPORTING FOR DEBUGGING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Simple error logging function
function writeErrorLog($message)
{
    $log = date('Y-m-d H:i:s') . " - " . $message . PHP_EOL;
    file_put_contents(__DIR__ . '/error.log', $log, FILE_APPEND | LOCK_EX);
}

writeErrorLog("Script started - tokenmid.php");

// Set header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check required files
if (!file_exists(__DIR__ . '/payment/Midtrans.php')) {
    writeErrorLog("ERROR: Midtrans.php not found");
    echo json_encode(['error' => 'Midtrans library not found']);
    exit;
}

if (!file_exists(__DIR__ . '/../include/config.php')) {
    writeErrorLog("ERROR: config.php not found");
    echo json_encode(['error' => 'Database config not found']);
    exit;
}

require_once __DIR__ . '/payment/Midtrans.php';
require_once __DIR__ . '/../include/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    writeErrorLog("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $price = isset($_POST['amount']) ? trim($_POST['amount']) : '';
    $notlp = isset($_POST['notlp']) ? trim($_POST['notlp']) : '';
    $package = isset($_POST['package']) ? trim($_POST['package']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $user_info = isset($_POST['user_info']) ? trim($_POST['user_info']) : '';
    // Cek format email


    // Cek hanya gmail
    $domain = strtolower(substr(strrchr($email, "@"), 1));
    if ($domain !== 'gmail.com') {
        echo json_encode(['error' => 'Sepertinya ada yang salah dengan email Anda. Mohon gunakan email Gmail.']);
        exit;
    }
    // Validasi input
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Email tidak valid atau kosong']);
        exit;
    }

    if (empty($notlp) || strlen($notlp) < 10 || !preg_match('/^[0-9+\-\s()]+$/', $notlp)) {
        echo json_encode(['error' => 'Nomor telepon tidak valid (minimal 10 digit)']);
        exit;
    }

    if (empty($username) || strlen($username) < 3) {
        echo json_encode(['error' => 'Nama lengkap minimal 3 karakter']);
        exit;
    }

    if (empty($price) || !is_numeric($price) || $price <= 0) {
        echo json_encode(['error' => 'Jumlah pembayaran tidak valid']);
        exit;
    }

    if (!isset($con) || !$con || mysqli_connect_error()) {
        throw new Exception('Koneksi database gagal: ' . mysqli_connect_error());
    }

    // Escape input
    $price = mysqli_real_escape_string($con, $price);
    $email = mysqli_real_escape_string($con, $email);
    $notlp = mysqli_real_escape_string($con, $notlp);
    $username = mysqli_real_escape_string($con, $username);
    $user_info = mysqli_real_escape_string($con, $user_info);
    $package = mysqli_real_escape_string($con, $package);

    // Generate order ID
    $order_id = 'DP-' . date('YmdHis') . '-' . mt_rand(1000, 9999);

    // Cek apakah email atau username (yang sama dengan email) sudah ada di userx
    $query = "SELECT id, status, username FROM userx WHERE email = '$email' OR username = '$email'";
    $result = mysqli_query($con, $query);

    if (!$result) {
        throw new Exception('Query error: ' . mysqli_error($con));
    }

    $existingUser = mysqli_fetch_assoc($result);

    if ($existingUser && $existingUser['email'] === $email && $existingUser['status'] === 'active') {
        echo json_encode(['error' => 'Email sudah terdaftar dengan membership aktif']);
        exit;
    }

    if ($existingUser && $existingUser['username'] === $email) {
        echo json_encode(['error' => 'Email ini sudah digunakan sebagai username. Gunakan email lain.']);
        exit;
    }

    // Cek transaksi pending yang belum expired (1 jam)
    $query = "SELECT order_id FROM transactions 
              WHERE email = '$email' AND status = 'pending' 
              AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
              ORDER BY created_at DESC LIMIT 1";
    $result = mysqli_query($con, $query);

    if (!$result) {
        throw new Exception('Query error: ' . mysqli_error($con));
    }

    $pendingTransaction = mysqli_fetch_assoc($result);

    if ($pendingTransaction) {
        echo json_encode([
            'error' => 'Anda memiliki transaksi pending. Silakan selesaikan pembayaran atau tunggu 1 jam.',
            'pending_order_id' => $pendingTransaction['order_id']
        ]);
        exit;
    }

    // Simpan transaksi baru
    $amount = $price;
    $query = "INSERT INTO transactions (user_info,order_id, email, phone, full_name, amount, status, created_at, package) 
              VALUES ('$user_info','$order_id', '$email', '$notlp', '$username', $amount, 'pending', NOW(), '$package')";
    $result = mysqli_query($con, $query);

    if (!$result) {
        throw new Exception('Gagal menyimpan transaksi: ' . mysqli_error($con));
    }

    // Simpan ke session
    $_SESSION['pending_registration'] = [
        'order_id' => $order_id,
        'email' => $email,
        'notlp' => $notlp,
        'username' => $username,
        'amount' => $amount,
        'package' => $package,
        'timestamp' => time()
    ];

    // Kirim response sukses
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'redirect_url' => 'checkout.php?order_id=' . $order_id,
        'message' => 'Data transaksi berhasil disimpan'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'order_id' => isset($order_id) ? $order_id : null
    ]);
}

// Close koneksi
if (isset($con)) {
    mysqli_close($con);
}
?>