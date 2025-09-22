<?php
require_once '../config/midtrans.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$order_id = trim($_POST['order_id'] ?? '');

if (empty($order_id)) {
    echo json_encode(['error' => 'Order ID required']);
    exit;
}

try {
    // Cek status dari database
    $pdo = getDbConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $transaction = $stmt->fetch();
    
    if (!$transaction) {
        echo json_encode(['error' => 'Transaction not found']);
        exit;
    }
    
    // Jika status masih pending, cek ke Midtrans
    if ($transaction['status'] === 'pending') {
        $midtrans_status = checkMidtransStatus($order_id);
        
        if ($midtrans_status['success']) {
            $new_status = $midtrans_status['status'];
            
            // Update status di database
            $stmt = $pdo->prepare("UPDATE transactions SET status = ?, updated_at = NOW() WHERE order_id = ?");
            $stmt->execute([$new_status, $order_id]);
            
            // Jika berhasil, aktifkan membership
            if ($new_status === 'success') {
                activateMembership($pdo, $transaction);
            }
            
            echo json_encode([
                'status' => $new_status,
                'message' => $new_status === 'success' ? 'Payment successful' : 'Payment ' . $new_status,
                'updated' => true
            ]);
        } else {
            echo json_encode([
                'status' => $transaction['status'],
                'message' => 'Status unchanged',
                'updated' => false
            ]);
        }
    } else {
        echo json_encode([
            'status' => $transaction['status'],
            'message' => 'Status: ' . $transaction['status'],
            'updated' => false
        ]);
    }
    
} catch (Exception $e) {
    logError("Error in check_status.php", ['error' => $e->getMessage(), 'order_id' => $order_id]);
    echo json_encode(['error' => 'Internal server error']);
}

function checkMidtransStatus($order_id) {
    try {
        $url = MidtransConfig::getBaseUrl() . '/status/' . $order_id;
        $serverKey = MidtransConfig::SERVER_KEY;
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($serverKey . ':')
            ],
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            $transaction_status = $result['transaction_status'] ?? '';
            $fraud_status = $result['fraud_status'] ?? '';
            
            $status = 'pending';
            if ($transaction_status === 'settlement' || 
                ($transaction_status === 'capture' && $fraud_status === 'accept')) {
                $status = 'success';
            } elseif (in_array($transaction_status, ['cancel', 'deny', 'expire', 'failure'])) {
                $status = 'failed';
            }
            
            return ['success' => true, 'status' => $status, 'data' => $result];
        }
        
        return ['success' => false, 'status' => 'pending'];
    } catch (Exception $e) {
        logError("Error checking Midtrans status", ['error' => $e->getMessage(), 'order_id' => $order_id]);
        return ['success' => false, 'status' => 'error'];
    }
}

function activateMembership($pdo, $transaction) {
    try {
        $password = generateRandomPassword();
        $hashedPassword = hashPassword($password);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (email, phone, full_name, password, membership_status, membership_expired, created_at) 
            VALUES (?, ?, ?, ?, 'active', DATE_ADD(NOW(), INTERVAL 1 YEAR), NOW())
            ON DUPLICATE KEY UPDATE 
            membership_status = 'active', 
            membership_expired = DATE_ADD(NOW(), INTERVAL 1 YEAR),
            password = VALUES(password),
            updated_at = NOW()
        ");
        $stmt->execute([
            $transaction['email'], 
            $transaction['phone'], 
            $transaction['full_name'],
            $hashedPassword
        ]);
        
        // Send welcome email (optional)
        // sendWelcomeEmail($transaction['email'], $transaction['full_name'], $transaction['order_id'], $password);
        
    } catch (Exception $e) {
        logError("Error activating membership", ['error' => $e->getMessage(), 'order_id' => $transaction['order_id']]);
        throw $e;
    }
}
?>