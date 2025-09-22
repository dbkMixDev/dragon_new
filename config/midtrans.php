<?php
session_start();
require_once 'payment/Midtrans.php';

// Set header untuk JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Validasi dan sanitasi input
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $notlp = trim($_POST['notlp'] ?? '');
    $username = trim($_POST['username'] ?? '');

    // Validasi email
    if (!$email) {
        echo json_encode(['error' => 'Email tidak valid atau kosong']);
        exit;
    }

    // Validasi nomor telepon
    if (empty($notlp) || strlen($notlp) < 10 || !preg_match('/^[0-9+\-\s()]+$/', $notlp)) {
        echo json_encode(['error' => 'Nomor telepon tidak valid (minimal 10 digit)']);
        exit;
    }

    // Validasi nama
    if (empty($username) || strlen($username) < 3) {
        echo json_encode(['error' => 'Nama lengkap minimal 3 karakter']);
        exit;
    }

    // Generate order ID unik
    $order_id = 'DP-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
    
    // Koneksi database
    $pdo = getDbConnection();
    if (!$pdo) {
        throw new Exception('Koneksi database gagal');
    }

    // Cek apakah email sudah terdaftar dan aktif
    $stmt = $pdo->prepare("SELECT id, membership_status FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();
    
    if ($existingUser && $existingUser['membership_status'] === 'active') {
        echo json_encode(['error' => 'Email sudah terdaftar dengan membership aktif']);
        exit;
    }

    // Cek transaksi pending yang belum expired (1 jam)
    $stmt = $pdo->prepare("
        SELECT order_id FROM transactions 
        WHERE email = ? AND status = 'pending' AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$email]);
    $pendingTransaction = $stmt->fetch();
    
    if ($pendingTransaction) {
        echo json_encode([
            'error' => 'Anda memiliki transaksi pending. Silakan selesaikan pembayaran atau tunggu 1 jam untuk membuat transaksi baru.',
            'pending_order_id' => $pendingTransaction['order_id']
        ]);
        exit;
    }

    // Simpan data transaksi
    $stmt = $pdo->prepare("
        INSERT INTO transactions (order_id, email, phone, full_name, amount, status, created_at) 
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$order_id, $email, $notlp, $username, 50000]);

    // Simpan di session sebagai backup
    $_SESSION['pending_registration'] = [
        'order_id' => $order_id,
        'email' => $email,
        'notlp' => $notlp,
        'username' => $username,
        'amount' => 50000,
        'timestamp' => time()
    ];

    // Parameter untuk Midtrans Snap
    $params = [
        'transaction_details' => [
            'order_id' => $order_id,
            'gross_amount' => 50000
        ],
        'customer_details' => [
            'first_name' => $username,
            'email' => $email,
            'phone' => $notlp
        ],
        'item_details' => [
            [
                'id' => 'membership-dragonplay',
                'price' => 50000,
                'quantity' => 1,
                'name' => 'Dragon Play Premium Membership',
                'brand' => 'Dragon Play',
                'category' => 'Gaming Membership'
            ]
        ],
        'callbacks' => [
            'finish' => 'http://' . $_SERVER['HTTP_HOST'] . '/success.php?order_id=' . $order_id
        ],
        'expiry' => [
            'start_time' => date('Y-m-d H:i:s O'),
            'unit' => 'hour',
            'duration' => 1
        ],
        'credit_card' => [
            'secure' => true
        ]
    ];

    // Request ke Midtrans Snap API
    $snapUrl = MidtransConfig::getSnapUrl();
    $serverKey = MidtransConfig::SERVER_KEY;

    if (empty($serverKey) || $serverKey === 'Mid-server-DXD4mYC-7COHSlgYJH5cGfqa') {
        throw new Exception('Server key Midtrans belum dikonfigurasi. Silakan periksa config/midtrans.php');
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $snapUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($serverKey . ':')
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => MidtransConfig::IS_PRODUCTION // Only verify SSL in production
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    // Handle cURL errors
    if ($curlError) {
        logError("CURL Error in tokenmid.php", ['error' => $curlError, 'order_id' => $order_id]);
        throw new Exception('Koneksi ke server pembayaran gagal. Silakan coba lagi.');
    }

    // Handle HTTP errors
    if ($httpCode !== 201) {
        logError("Midtrans API Error", [
            'http_code' => $httpCode, 
            'response' => $response, 
            'order_id' => $order_id
        ]);
        
        if ($httpCode === 401) {
            throw new Exception('Konfigurasi Midtrans tidak valid. Silakan hubungi administrator.');
        } else if ($httpCode >= 500) {
            throw new Exception('Server pembayaran sedang bermasalah. Silakan coba lagi nanti.');
        } else {
            throw new Exception('Gagal membuat transaksi pembayaran. Silakan coba lagi.');
        }
    }

    // Parse response
    $midtransResponse = json_decode($response, true);
    
    if (!$midtransResponse || !isset($midtransResponse['token'])) {
        logError("Invalid Midtrans Response", ['response' => $response, 'order_id' => $order_id]);
        throw new Exception('Response pembayaran tidak valid. Silakan coba lagi.');
    }

    // Update transaction dengan token
    $stmt = $pdo->prepare("UPDATE transactions SET payment_token = ? WHERE order_id = ?");
    $stmt->execute([$midtransResponse['token'], $order_id]);

    // Return success response - REMOVED THE PROBLEMATIC HEADER REDIRECT
    echo json_encode([
        'success' => true,
        'token' => $midtransResponse['token'],
        'order_id' => $order_id,
        'redirect_url' => $midtransResponse['redirect_url'] ?? null,
        'message' => 'Token pembayaran berhasil dibuat'
    ]);

} catch (Exception $e) {
    // Log error
    logError("Exception in tokenmid.php", [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'input' => $_POST
    ]);
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'order_id' => $order_id ?? null
    ]);
}
?>