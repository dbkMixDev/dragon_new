<?php
require_once '../include/config.php'; // sesuaikan path

$data = json_decode(file_get_contents("php://input"), true);

$order_id = $data['order_id'] ?? '';
$payment_type = $data['payment_type'] ?? '';
$va_number = '';

// Ambil VA number dari struktur Midtrans
if (isset($data['va_numbers'][0]['va_number'])) {
    $va_number = $data['va_numbers'][0]['va_number'];
} elseif (isset($data['permata_va_number'])) {
    $va_number = $data['permata_va_number'];
}

// Simpan ke DB
if ($order_id && $payment_type && $va_number) {
    $stmt = $con->prepare("UPDATE transactions SET payment_type = ?, va_number = ?, midtrans_response = ? WHERE order_id = ?");
    $json = json_encode($data);
    $stmt->bind_param("ssss", $payment_type, $va_number, $json, $order_id);
    $stmt->execute();
    $stmt->close();
}
?>
