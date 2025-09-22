<?php
// callback.php - Handle payment notifications from Midtrans

header('Content-Type: application/json');

// Get JSON input
$input = file_get_contents('php://input');
$notification = json_decode($input, true);

// Your server key (same as in gettoken.php)
$server_key = 'Mid-server-DXD4mYC-7COHSlgYJH5cGfqa';

// Verify signature
$signature_key = hash('sha512', $notification['order_id'] . $notification['status_code'] . $notification['gross_amount'] . $server_key);

if ($signature_key !== $notification['signature_key']) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
    exit;
}

// Extract notification data
$order_id = $notification['order_id'];
$transaction_status = $notification['transaction_status'];
$payment_type = $notification['payment_type'];
$fraud_status = isset($notification['fraud_status']) ? $notification['fraud_status'] : '';

// Log the notification
$log_data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'order_id' => $order_id,
    'transaction_status' => $transaction_status,
    'payment_type' => $payment_type,
    'fraud_status' => $fraud_status,
    'full_notification' => $notification
];
file_put_contents("logs/payment_callback_log.txt", json_encode($log_data) . "\n", FILE_APPEND | LOCK_EX);

// Handle different transaction statuses
switch ($transaction_status) {
    case 'capture':
        if ($payment_type == 'credit_card') {
            if ($fraud_status == 'challenge') {
                // Transaction is challenged by FDS
                updatePaymentStatus($order_id, 'challenge');
            } else {
                // Transaction is successful
                updatePaymentStatus($order_id, 'success');
                activateMembership($order_id);
            }
        }
        break;
        
    case 'settlement':
        // Transaction is successful
        updatePaymentStatus($order_id, 'success');
        activateMembership($order_id);
        break;
        
    case 'pending':
        // Transaction is pending
        updatePaymentStatus($order_id, 'pending');
        break;
        
    case 'deny':
    case 'expire':
    case 'cancel':
        // Transaction is failed
        updatePaymentStatus($order_id, 'failed');
        break;
}

// Send response to Midtrans
echo json_encode(['status' => 'OK']);

// Function to update payment status (you can modify this to use your database)
function updatePaymentStatus($order_id, $status) {
    // Example: Update database
    // $pdo = new PDO('mysql:host=localhost;dbname=dragonplay', $username, $password);
    // $stmt = $pdo->prepare("UPDATE payments SET status = ? WHERE order_id = ?");
    // $stmt->execute([$status, $order_id]);
    
    // For now, just log it
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'order_id' => $order_id,
        'new_status' => $status,
        'action' => 'status_updated'
    ];
    file_put_contents("logs/status_updates.txt", json_encode($log) . "\n", FILE_APPEND | LOCK_EX);
}

// Function to activate membership
function activateMembership($order_id) {
    // Example: Activate user membership
    // $pdo = new PDO('mysql:host=localhost;dbname=dragonplay', $username, $password);
    // $stmt = $pdo->prepare("UPDATE users SET membership_status = 'active', membership_date = NOW() WHERE order_id = ?");
    // $stmt->execute([$order_id]);
    
    // For now, just log it
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'order_id' => $order_id,
        'action' => 'membership_activated'
    ];
    file_put_contents("logs/membership_activations.txt", json_encode($log) . "\n", FILE_APPEND | LOCK_EX);
}
?>