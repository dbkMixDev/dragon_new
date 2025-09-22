<?php
// process_payment.php
header('Content-Type: application/json');
include '../include/config.php'; // sesuaikan path config.php
session_start();
$userid = $_SESSION['user_id'] ?? '';
$username = $_SESSION['username'] ?? '';
// Ambil input
$booking_id = isset($_POST['booking_id']) ? $con->real_escape_string($_POST['booking_id']) : '';
$payment_method = isset($_POST['payment_method']) ? $con->real_escape_string($_POST['payment_method']) : '';
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

// Validasi input
if (empty($booking_id) || empty($payment_method) || $amount <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Input tidak valid'
    ]);
    exit;
}

// Ambil data booking
$sql = "SELECT payment_amount, payment_status FROM bookings WHERE id = '$booking_id' LIMIT 1";
$res = $con->query($sql);

if ($res->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Booking tidak ditemukan'
    ]);
    exit;
}

$row = $res->fetch_assoc();
$paid_amount = floatval($row['payment_amount']);

// Hitung pembayaran baru
$new_paid_amount = $paid_amount + $amount;

// Tentukan status pembayaran
// Kalau ada kolom total harga (misal `total_price`) silakan bandingkan.
// Kalau tidak ada, kita cukup catat 'dibayar' dan update amount.
$status = ($row['payment_status'] === 'lunas') ? 'lunas' : 'dp';

// Update data booking
$update = "UPDATE bookings 
           SET payment_amount = $new_paid_amount,
               payment_method = '$payment_method',
               acc_admin = '$userid',
               payment_status = '$status',
               updated_at = NOW()
           WHERE id = '$booking_id'";

if ($con->query($update)) {
    echo json_encode([
        'success' => true,
        'message' => 'Pembayaran berhasil',
        'status' => $status,
        'paid' => $new_paid_amount
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal update database: ' . $con->error
    ]);
}
