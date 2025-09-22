<?php
// controller/update_payment_status.php
include '../include/config.php';

if ($_POST && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $payment_result = isset($_POST['payment_result']) ? $_POST['payment_result'] : '';
    
    // Update order status in database
    $stmt = $con->prepare("UPDATE orders SET status = ?, payment_result = ?, updated_at = NOW() WHERE order_id = ?");
    $stmt->bind_param("sss", $status, $payment_result, $order_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
}
?>