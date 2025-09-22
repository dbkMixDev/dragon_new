<?php
namespace Midtrans;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include Midtrans configuration
require_once dirname(__FILE__) . '/payment/Midtrans.php';

// Set your server key
Config::$serverKey = 'Mid-server-DXD4mYC-7COHSlgYJH5cGfqa';
Config::$clientKey = 'Mid-client-SqCQO-tyEQfQqU4t';
Config::$isSanitized = Config::$is3ds = true;

// Include database connection
include "../include/config.php";

try {
    $order_id = $_GET['order_id'] ?? '';
    
    if (empty($order_id)) {
        throw new Exception('Order ID is required');
    }
    
    // Get transaction from database
    $query = "SELECT * FROM transactions WHERE order_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $transaction = mysqli_fetch_assoc($result);
    
    if (!$transaction) {
        throw new Exception('Transaction not found');
    }
    
    // Check status from Midtrans
    try {
$midtransStatus = \Midtrans\Transaction::status($order_id);
        $transactionStatus = $midtransStatus->transaction_status;
        $fraudStatus = $midtransStatus->fraud_status ?? '';
        
        // Determine final status
        $finalStatus = 'pending';
        $statusMessage = 'Pembayaran sedang diproses';
        
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $finalStatus = 'pending';
                $statusMessage = 'Pembayaran dalam review fraud';
            } else if ($fraudStatus == 'accept') {
                $finalStatus = 'success';
                $statusMessage = 'Pembayaran berhasil';
            }
        } else if ($transactionStatus == 'settlement') {
            $finalStatus = 'success';
            $statusMessage = 'Pembayaran berhasil dan telah diselesaikan';
        } else if ($transactionStatus == 'pending') {
            $finalStatus = 'pending';
            $statusMessage = 'Menunggu pembayaran';
        } else if ($transactionStatus == 'deny') {
            $finalStatus = 'failed';
            $statusMessage = 'Pembayaran ditolak';
        } else if ($transactionStatus == 'expire') {
            $finalStatus = 'failed';
            $statusMessage = 'Pembayaran kadaluarsa';
        } else if ($transactionStatus == 'cancel') {
            $finalStatus = 'failed';
            $statusMessage = 'Pembayaran dibatalkan';
        }
        
        $payment_type = $midtransStatus->payment_type ?? '';
$va_number = '';

// Ambil VA number jika ada
if (!empty($midtransStatus->va_numbers)) {
    $va_number = $midtransStatus->va_numbers[0]->va_number ?? '';
} elseif (!empty($midtransStatus->bill_key)) {
    $va_number = $midtransStatus->bill_key;
}

if (
    $transaction['status'] !== $finalStatus ||
    $transaction['payment_type'] !== $payment_type ||
    $transaction['va_number'] !== $va_number
) {
    $updateQuery = "UPDATE transactions SET status = ?, payment_type = ?, va_number = ?, updated_at = NOW() WHERE order_id = ?";
    $updateStmt = mysqli_prepare($con, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, "ssss", $finalStatus, $payment_type, $va_number, $order_id);
    mysqli_stmt_execute($updateStmt);
    
    if ($finalStatus === 'success') {
        activateUserAccount($transaction, $con);
    }
}

        
        // Response data
        $response = [
            'success' => true,
            'status' => $finalStatus,
            'message' => $statusMessage,
            'order_id' => $order_id,
            'transaction_details' => [
                'order_id' => $midtransStatus->order_id,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'status_message' => $midtransStatus->status_message ?? '',
                'payment_type' => $midtransStatus->payment_type ?? '',
                'transaction_time' => $midtransStatus->transaction_time ?? '',
                'gross_amount' => $midtransStatus->gross_amount ?? 0
            ],
            'user_data' => [
                'name' => $transaction['full_name'],
                'email' => $transaction['email'],
                'package' => $transaction['package'] ?? 'starter',
                'amount' => $transaction['amount']
            ]
        ];
        
        // Additional data based on status
        if ($finalStatus === 'success') {
            $response['redirect_url'] = "payment-success.php?order_id=" . $order_id;
            $response['activation_info'] = [
                'activated_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+1 year')),
                'features_activated' => getPackageFeatures($transaction['package'] ?? 'starter')
            ];
        } else if ($finalStatus === 'failed') {
            $response['redirect_url'] = "payment-failed.php?order_id=" . $order_id;
            $response['retry_options'] = [
                'can_retry' => true,
                'retry_url' => "checkout.php?order_id=" . $order_id,
                'alternative_methods' => ['bank_transfer', 'e_wallet', 'convenience_store']
            ];
        } else {
            $response['redirect_url'] = "payment-pending.php?order_id=" . $order_id;
            $response['payment_instructions'] = getPaymentInstructions($midtransStatus->payment_type ?? '');
        }
        
    } catch (Exception $e) {
        // If Midtrans API call fails, check database status
        $finalStatus = $transaction['status'] ?? 'pending';
        $response = [
            'success' => true,
            'status' => $finalStatus,
            'message' => 'Status dari database: ' . ucfirst($finalStatus),
            'order_id' => $order_id,
            'api_error' => $e->getMessage(),
            'user_data' => [
                'name' => $transaction['full_name'],
                'email' => $transaction['email'],
                'package' => $transaction['package'] ?? 'starter',
                'amount' => $transaction['amount']
            ]
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => 'CHECK_STATUS_ERROR'
    ]);
}

// Function to activate user account
function activateUserAccount($transaction, $con) {
    try {
        // Create or update user premium status
        $email = $transaction['email'];
        $package = $transaction['package'] ?? 'starter';
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 year'));
        
        // Check if user exists in users table
        $userQuery = "SELECT id FROM users WHERE email = ?";
        $userStmt = mysqli_prepare($con, $userQuery);
        mysqli_stmt_bind_param($userStmt, "s", $email);
        mysqli_stmt_execute($userStmt);
        $userResult = mysqli_stmt_get_result($userStmt);
        $user = mysqli_fetch_assoc($userResult);
        
        if ($user) {
            // Update existing user
            $updateUserQuery = "UPDATE users SET premium_package = ?, premium_expires_at = ?, updated_at = NOW() WHERE email = ?";
            $updateUserStmt = mysqli_prepare($con, $updateUserQuery);
            mysqli_stmt_bind_param($updateUserStmt, "sss", $package, $expires_at, $email);
            mysqli_stmt_execute($updateUserStmt);
        } else {
            // Create new user
            $insertUserQuery = "INSERT INTO users (full_name, email, premium_package, premium_expires_at, created_at) VALUES (?, ?, ?, ?, NOW())";
            $insertUserStmt = mysqli_prepare($con, $insertUserQuery);
            mysqli_stmt_bind_param($insertUserStmt, "ssss", $transaction['full_name'], $email, $package, $expires_at);
            mysqli_stmt_execute($insertUserStmt);
        }
        
        // Send activation email (implement this function)
        sendActivationEmail($transaction);
        
        return true;
    } catch (Exception $e) {
        error_log("Error activating user account: " . $e->getMessage());
        return false;
    }
}

// Function to get package features
function getPackageFeatures($package) {
    $features = [
        'starter' => [
            'android_tv_units' => 5,
            'smart_tv_unlimited' => true,
            'billiard_unlimited' => true,
            'web_portal' => true,
            'booking_online' => true,
            'employee_accounts' => 3,
            'community_forum' => true,
            'qr_code_interactive' => true,
            'branches' => 1
        ],
        'business' => [
            'android_tv_units' => 15,
            'smart_tv_unlimited' => true,
            'billiard_unlimited' => true,
            'web_portal' => true,
            'booking_online' => true,
            'employee_accounts' => 3,
            'community_forum' => true,
            'qr_code_interactive' => true,
            'branches' => 1
        ],
        'professional' => [
            'android_tv_units' => 25,
            'smart_tv_unlimited' => true,
            'billiard_unlimited' => true,
            'web_portal' => true,
            'booking_online' => true,
            'employee_accounts' => 3,
            'community_forum' => true,
            'qr_code_interactive' => true,
            'branches' => 1
        ],
        'enterprise' => [
            'android_tv_units' => 40,
            'smart_tv_unlimited' => true,
            'billiard_unlimited' => true,
            'web_portal' => true,
            'booking_online' => true,
            'employee_accounts' => 3,
            'community_forum' => true,
            'qr_code_interactive' => true,
            'branches' => 1,
            'priority_support' => true
        ]
    ];
    
    return $features[$package] ?? $features['starter'];
}

// Function to get payment instructions
function getPaymentInstructions($paymentType) {
    $instructions = [
        'bank_transfer' => [
            'title' => 'Transfer Bank',
            'steps' => [
                'Buka aplikasi mobile banking atau internet banking',
                'Pilih menu transfer',
                'Masukkan nomor rekening yang tertera',
                'Masukkan jumlah yang harus dibayar',
                'Konfirmasi transfer'
            ]
        ],
        'credit_card' => [
            'title' => 'Kartu Kredit',
            'steps' => [
                'Masukkan nomor kartu kredit',
                'Masukkan tanggal kadaluarsa',
                'Masukkan CVV',
                'Konfirmasi pembayaran'
            ]
        ],
        'echannel' => [
            'title' => 'ATM Mandiri',
            'steps' => [
                'Masukkan kartu ATM dan PIN',
                'Pilih menu Bayar/Beli',
                'Pilih menu Lainnya',
                'Pilih e-Commerce',
                'Masukkan kode perusahaan dan kode bayar'
            ]
        ],
        'bca_va' => [
            'title' => 'Virtual Account BCA',
            'steps' => [
                'Buka aplikasi BCA Mobile atau klik BCA',
                'Pilih m-Transfer',
                'Pilih BCA Virtual Account',
                'Masukkan nomor Virtual Account',
                'Konfirmasi pembayaran'
            ]
        ]
    ];
    
    return $instructions[$paymentType] ?? [
        'title' => 'Pembayaran',
        'steps' => ['Ikuti instruksi pembayaran yang tersedia']
    ];
}

// Function to send activation email
function sendActivationEmail($transaction) {
    try {
        $to = $transaction['email'];
        $subject = 'Dragon Play Premium - Akun Anda Telah Aktif!';
        $package = ucfirst($transaction['package'] ?? 'starter');
        
        $message = "
        <html>
        <head>
            <title>Dragon Play Premium Activated</title>
        </head>
        <body>
            <h2>Selamat! Akun Premium Anda Sudah Aktif</h2>
            <p>Halo {$transaction['full_name']},</p>
            <p>Terima kasih telah bergabung dengan Dragon Play Premium. Akun Anda telah berhasil diaktivasi dengan paket <strong>{$package}</strong>.</p>
            
            <h3>Detail Akun:</h3>
            <ul>
                <li>Nama: {$transaction['full_name']}</li>
                <li>Email: {$transaction['email']}</li>
                <li>Paket: {$package}</li>
                <li>Order ID: {$transaction['order_id']}</li>
                <li>Berlaku hingga: " . date('d M Y', strtotime('+1 year')) . "</li>
            </ul>
            
            <p>Anda sekarang dapat menikmati semua fitur premium yang tersedia.</p>
            
            <p>Salam,<br>Tim Dragon Play</p>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: noreply@dragonplay.com' . "\r\n";
        
        mail($to, $subject, $message, $headers);
        
        return true;
    } catch (Exception $e) {
        error_log("Error sending activation email: " . $e->getMessage());
        return false;
    }
}
?>