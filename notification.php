<?php
require_once 'config/midtrans.php';

// Log semua request untuk debugging
$rawInput = file_get_contents('php://input');
logError("Midtrans Notification Received", ['raw_input' => $rawInput]);

// Validasi JSON
$notification = json_decode($rawInput, true);
if (!$notification) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

try {
    // Ambil data dari notification
    $order_id = $notification['order_id'] ?? '';
    $status_code = $notification['status_code'] ?? '';
    $gross_amount = $notification['gross_amount'] ?? '';
    $signature_key = $notification['signature_key'] ?? '';
    $transaction_status = $notification['transaction_status'] ?? '';
    $fraud_status = $notification['fraud_status'] ?? '';
    $payment_type = $notification['payment_type'] ?? '';
    $transaction_time = $notification['transaction_time'] ?? '';

    // Validasi data wajib
    if (empty($order_id) || empty($status_code) || empty($gross_amount) || empty($signature_key)) {
        logError("Missing required fields in notification", $notification);
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    // Validasi signature key untuk keamanan
    $server_key = MidtransConfig::SERVER_KEY;
    $expected_signature = hash('sha512', $order_id . $status_code . $gross_amount . $server_key);

    if ($signature_key !== $expected_signature) {
        logError("Invalid signature", [
            'order_id' => $order_id,
            'received_signature' => $signature_key,
            'expected_signature' => $expected_signature
        ]);
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
        exit;
    }

    // Koneksi database
    $pdo = getDbConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    // Cari transaksi berdasarkan order_id
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $transaction = $stmt->fetch();

    if (!$transaction) {
        logError("Transaction not found", ['order_id' => $order_id]);
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
        exit;
    }

    // Tentukan status baru berdasarkan response Midtrans
    $new_status = determineTransactionStatus($transaction_status, $fraud_status);
    
    logError("Processing transaction status update", [
        'order_id' => $order_id,
        'old_status' => $transaction['status'],
        'new_status' => $new_status,
        'transaction_status' => $transaction_status,
        'fraud_status' => $fraud_status
    ]);

    // Update status transaksi
    $stmt = $pdo->prepare("
        UPDATE transactions 
        SET status = ?, payment_type = ?, transaction_time = ?, updated_at = NOW() 
        WHERE order_id = ?
    ");
    $stmt->execute([$new_status, $payment_type, $transaction_time, $order_id]);

    // Jika pembayaran berhasil, proses membership
    if ($new_status === 'success' && $transaction['status'] !== 'success') {
        processSuccessfulPayment($pdo, $transaction);
        logError("Payment successful, membership activated", ['order_id' => $order_id]);
    } else if ($new_status === 'failed') {
        logError("Payment failed", ['order_id' => $order_id, 'reason' => $transaction_status]);
    }

    // Response sukses ke Midtrans
    echo json_encode(['status' => 'success', 'message' => 'Notification processed']);

} catch (Exception $e) {
    logError("Error processing notification", [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'notification' => $notification
    ]);
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
}

// Function untuk menentukan status transaksi
function determineTransactionStatus($transaction_status, $fraud_status) {
    switch ($transaction_status) {
        case 'capture':
            return ($fraud_status === 'accept') ? 'success' : 'challenge';
        case 'settlement':
            return 'success';
        case 'cancel':
        case 'deny':
        case 'expire':
        case 'failure':
            return 'failed';
        case 'pending':
        default:
            return 'pending';
    }
}

// Function untuk proses pembayaran berhasil
function processSuccessfulPayment($pdo, $transaction) {
    try {
        // Generate password untuk user baru
        $password = generateRandomPassword();
        $hashedPassword = hashPassword($password);
        
        // Buat atau update user
        $stmt = $pdo->prepare("
            INSERT INTO users (email, phone, full_name, password, membership_status, membership_expired, created_at) 
            VALUES (?, ?, ?, ?, 'active', DATE_ADD(NOW(), INTERVAL 1 YEAR), NOW())
            ON DUPLICATE KEY UPDATE 
            password = VALUES(password),
            membership_status = 'active', 
            membership_expired = DATE_ADD(NOW(), INTERVAL 1 YEAR),
            updated_at = NOW()
        ");
        $stmt->execute([
            $transaction['email'], 
            $transaction['phone'], 
            $transaction['full_name'],
            $hashedPassword
        ]);
        
        // Kirim email welcome dengan password
        sendWelcomeEmail(
            $transaction['email'], 
            $transaction['full_name'], 
            $transaction['order_id'],
            $password
        );
        
        logError("User account created/updated", [
            'email' => $transaction['email'],
            'order_id' => $transaction['order_id']
        ]);
        
    } catch (Exception $e) {
        logError("Error processing successful payment", [
            'error' => $e->getMessage(),
            'order_id' => $transaction['order_id']
        ]);
        throw $e;
    }
}

// Function untuk kirim email welcome
function sendWelcomeEmail($email, $name, $order_id, $password) {
    try {
        $subject = "🎉 Selamat Datang di Dragon Play - Akun Anda Sudah Aktif!";
        
        $message = createWelcomeEmailTemplate($name, $order_id, $email, $password);
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . EmailConfig::FROM_NAME . ' <' . EmailConfig::FROM_EMAIL . '>',
            'Reply-To: ' . EmailConfig::FROM_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $success = mail($email, $subject, $message, implode("\r\n", $headers));
        
        if ($success) {
            logError("Welcome email sent successfully", ['email' => $email, 'order_id' => $order_id]);
        } else {
            logError("Failed to send welcome email", ['email' => $email, 'order_id' => $order_id]);
        }
        
        return $success;
        
    } catch (Exception $e) {
        logError("Error sending welcome email", [
            'error' => $e->getMessage(),
            'email' => $email,
            'order_id' => $order_id
        ]);
        return false;
    }
}

// Function untuk template email
function createWelcomeEmailTemplate($name, $order_id, $email, $password) {
    $currentYear = date('Y');
    $expiryDate = date('d M Y', strtotime('+1 year'));
    $loginUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/login.php';
    $dashboardUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/dashboard.php';
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Welcome to Dragon Play</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
            .container { max-width: 600px; margin: 0 auto; background-color: white; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 40px 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; font-weight: bold; }
            .header p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.9; }
            .content { padding: 40px 30px; }
            .welcome-box { background: #e8f5e8; border-left: 4px solid #28a745; padding: 20px; margin: 20px 0; border-radius: 5px; }
            .info-box { background: #f8f9fa; border: 1px solid #dee2e6; padding: 20px; margin: 20px 0; border-radius: 8px; }
            .login-box { background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; margin: 20px 0; border-radius: 8px; }
            .button { display: inline-block; background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 15px 5px; font-weight: bold; text-align: center; }
            .button:hover { background: #218838; }
            .button-secondary { background: #007bff; }
            .button-secondary:hover { background: #0056b3; }
            .features { display: flex; flex-wrap: wrap; margin: 20px 0; }
            .feature { flex: 1; min-width: 150px; text-align: center; padding: 15px; }
            .feature-icon { font-size: 24px; margin-bottom: 10px; }
            .footer { background: #343a40; color: white; padding: 30px; text-align: center; }
            .footer p { margin: 5px 0; }
            .important { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0; }
            @media (max-width: 600px) {
                .features { flex-direction: column; }
                .content { padding: 20px 15px; }
                .header { padding: 30px 15px; }
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎮 Dragon Play</h1>
                <p>Selamat datang di dunia gaming premium!</p>
            </div>
            
            <div class='content'>
                <div class='welcome-box'>
                    <h2 style='color: #28a745; margin-top: 0;'>🎉 Pembayaran Berhasil!</h2>
                    <p><strong>Halo " . htmlspecialchars($name) . ",</strong></p>
                    <p>Terima kasih telah bergabung dengan Dragon Play! Membership premium Anda telah aktif dan siap digunakan.</p>
                </div>
                
                <div class='info-box'>
                    <h3>📋 Detail Transaksi</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Order ID:</strong></td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee;'>" . htmlspecialchars($order_id) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Email:</strong></td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee;'>" . htmlspecialchars($email) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Status:</strong></td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee;'><span style='color: #28a745; font-weight: bold;'>✅ Aktif</span></td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0;'><strong>Berlaku hingga:</strong></td>
                            <td style='padding: 8px 0;'>" . $expiryDate . "</td>
                        </tr>
                    </table>
                </div>
                
                <div class='login-box'>
                    <h3>🔐 Informasi Login Anda</h3>
                    <div class='important'>
                        <p><strong>⚠️ PENTING: Simpan informasi login ini dengan aman!</strong></p>
                    </div>
                    <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                    <p><strong>Password:</strong> <code style='background: #f8f9fa; padding: 4px 8px; border-radius: 4px; font-family: monospace;'>" . htmlspecialchars($password) . "</code></p>
                    <p><small style='color: #666;'>Anda dapat mengubah password setelah login pertama kali.</small></p>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . $loginUrl . "' class='button'>🚀 Login Sekarang</a>
                    <a href='" . $dashboardUrl . "' class='button button-secondary'>📊 Ke Dashboard</a>
                </div>
                
                <div class='info-box'>
                    <h3>🎮 Fitur Premium yang Bisa Anda Nikmati:</h3>
                    <div class='features'>
                        <div class='feature'>
                            <div class='feature-icon'>👑</div>
                            <strong>Akses Premium</strong>
                            <p>Semua game dan fitur premium tersedia</p>
                        </div>
                        <div class='feature'>
                            <div class='feature-icon'>🎯</div>
                            <strong>No Ads</strong>
                            <p>Gaming tanpa gangguan iklan</p>
                        </div>
                        <div class='feature'>
                            <div class='feature-icon'>⚡</div>
                            <strong>Priority Support</strong>
                            <p>Dukungan teknis 24/7</p>
                        </div>
                    </div>
                </div>
                
                <div class='info-box'>
                    <h3>🆘 Butuh Bantuan?</h3>
                    <p>Tim support kami siap membantu Anda:</p>
                    <ul>
                        <li>📧 Email: support@dragonplay.com</li>
                        <li>💬 Live Chat di website</li>
                        <li>📱 WhatsApp: +62 xxx-xxxx-xxxx</li>
                    </ul>
                </div>
                
                <p style='margin-top: 30px;'>Terima kasih telah mempercayai Dragon Play sebagai platform gaming Anda!</p>
                <p><strong>Happy Gaming! 🎮</strong></p>
                
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>
                    Email ini dikirim secara otomatis. Jika Anda tidak merasa melakukan pendaftaran, silakan hubungi support kami.
                </p>
            </div>
            
            <div class='footer'>
                <p><strong>Dragon Play</strong></p>
                <p>Platform Gaming Premium Indonesia</p>
                <p style='font-size: 12px; opacity: 0.8;'>© " . $currentYear . " Dragon Play. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
}
?>