<?php
header('Content-Type: application/json');
session_start();
require_once '../include/config.php'; // Sesuaikan path

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$booking_id = $input['id'];
$action = $input['action'];
$username = $_SESSION['username'];

try {
    switch ($action) {
        case 'mark_dp':
            $payment_amount = isset($input['payment_amount']) ? floatval($input['payment_amount']) : 0;
            $payment_method = isset($input['payment_method']) ? $input['payment_method'] : '';

            $sql = "UPDATE bookings SET 
                        status = '1', 
                        payment_status = 'dp',
                        payment_amount = ?,
                        payment_method = ?
                       
                    WHERE id = ? AND userx = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("dssi", $payment_amount, $payment_method, $booking_id, $username);
            break;

        case 'mark_full':
            $payment_amount = isset($input['payment_amount']) ? floatval($input['payment_amount']) : 0;
            $payment_method = isset($input['payment_method']) ? $input['payment_method'] : '';

            $sql = "UPDATE bookings SET 
                        status = '1', 
                        payment_status = 'full',
                        payment_amount = ?,
                        payment_method = ?,
                        updated_at = NOW()
                    WHERE id = ? AND userx = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("dssi", $payment_amount, $payment_method, $booking_id, $username);
            break;

        case 'mark_unpaid':
            $sql = "UPDATE bookings SET 
                        status = 'pending', 
                        payment_status = NULL,
                        payment_amount = NULL,
                        payment_method = NULL,
                        updated_at = NOW()
                    WHERE id = ? AND userx = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("is", $booking_id, $username);
            break;

        case 'cancel':
            $sql = "DELETE FROM bookings WHERE id = ? AND userx = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("is", $booking_id, $username);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No rows affected or booking not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$con->close();
?>