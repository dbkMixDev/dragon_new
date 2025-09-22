<?php
// Simple Midtrans notification handler without SDK

$server_key = 'Mid-server-DXD4mYC-7COHSlgYJH5cGfqa';

// Read the notification body
$json_result = file_get_contents('php://input');
$result = json_decode($json_result, true);

if (!$result) {
    http_response_code(400);
    exit('Invalid JSON');
}

// Verify signature key
$signature_key = hash('sha512', $result['order_id'] . $result['status_code'] . $result['gross_amount'] . $server_key);

if ($signature_key !== $result['signature_key']) {
    http_response_code(403);
    exit('Invalid signature');
}

// Extract notification data
$order_id = $result['order_id'];
$transaction_status = $result['transaction_status'];
$payment_type = $result['payment_type'];
$fraud_status = isset($result['fraud_status']) ? $result['fraud_status'] : null;
$gross_amount = $result['gross_amount'];

// Log the notification
error_log("Midtrans Notification: Order ID: $order_id, Status: $transaction_status, Type: $payment_type, Amount: $gross_amount");

// Process based on transaction status
$payment_status = '';

switch ($transaction_status) {
    case 'capture':
        if ($payment_type == 'credit_card') {
            if ($fraud_status == 'challenge') {
                $payment_status = 'challenge';
            } else {
                $payment_status = 'success';
            }
        } else {
            $payment_status = 'success';
        }
        break;
        
    case 'settlement':
        $payment_status = 'success';
        break;
        
    case 'pending':
        $payment_status = 'pending';
        break;
        
    case 'deny':
        $payment_status = 'denied';
        break;
        
    case 'expire':
        $payment_status = 'expired';
        break;
        
    case 'cancel':
        $payment_status = 'cancelled';
        break;
        
    default:
        $payment_status = 'unknown';
        break;
}

// Update payment status in your system
updatePaymentStatus($order_id, $payment_status, $result);

function updatePaymentStatus($order_id, $status, $notification_data) {
    // Simple file-based logging (you should implement database storage)
    $log_data = array(
        'timestamp' => date('Y-m-d H:i:s'),
        'order_id' => $order_id,
        'status' => $status,
        'notification_data' => $notification_data
    );
    
    $log_file = 'logs/payment_notifications.log';
    
    // Create logs directory if it doesn't exist
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    // Append to log file
    file_put_contents($log_file, json_encode($log_data) . "\n", FILE_APPEND | LOCK_EX);
    
    // If you have database connection, update here:
    /*
    $host = 'localhost';
    $username = 'your_db_username';
    $password = 'your_db_password';
    $database = 'dragon_play';
    
    try {
        $conn = new mysqli($host, $username, $password, $database);
        
        $stmt = $conn->prepare("UPDATE orders SET payment_status = ?, transaction_status = ?, payment_type = ?, updated_at = NOW() WHERE order_id = ?");
        $stmt->bind_param("ssss", $status, $notification_data['transaction_status'], $notification_data['payment_type'], $order_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        
        // Send email notification if payment successful
        if ($status == 'success') {
            sendSuccessEmail($order_id, $notification_data);
        }
        
    } catch (Exception $e) {
        error_log("Database update failed: " . $e->getMessage());
    }
    */
}

function sendSuccessEmail($order_id, $data) {
    // Simple email notification (implement as needed)
    $to = $data['customer_details']['email'] ?? '';
    $subject = "Payment Confirmation - Order #" . $order_id;
    $message = "Dear Customer,\n\nYour payment for order #" . $order_id . " has been successfully processed.\n\nThank you for your purchase!\n\nBest regards,\nDragon Play Team";
    $headers = "From: noreply@dragonplay.com\r\n";
    
    if ($to) {
        mail($to, $subject, $message, $headers);
    }
}

// Respond to Midtrans
http_response_code(200);
echo "OK";
?>