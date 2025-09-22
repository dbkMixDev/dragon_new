<?php
session_start();
header('Content-Type: application/json');

// Include koneksi database
include '../include/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$username = $_SESSION['username'];
$response = ['success' => false];

// Validasi input
if (!isset($_POST['no_ps'])) {
    $response['error'] = 'No unit tidak diberikan';
    echo json_encode($response);
    exit;
}

$no_ps = mysqli_real_escape_string($con, $_POST['no_ps']);

// Cek apakah unit milik user dan statusnya 'available'
$check_sql = "SELECT * FROM playstations WHERE no_ps = '$no_ps' AND userx = '$username'";
$check_result = $con->query($check_sql);

if ($check_result->num_rows == 0) {
    $response['error'] = 'Unit tidak ditemukan atau Anda tidak memiliki akses';
    echo json_encode($response);
    exit;
}

$unit = $check_result->fetch_assoc();
if ($unit['status'] != 'available') {
    $response['error'] = 'Unit tidak dalam status available, tidak dapat dihapus';
    echo json_encode($response);
    exit;
}

$check2_sql = "SELECT * FROM tb_trans WHERE id_ps = '$no_ps' AND userx = '$username' AND inv IS NULL";
$check2_result = $con->query($check2_sql);

if ($check2_result->num_rows > 0) {
    $response['error'] = 'Ada transaksi belum di bayar, tidak dapat dihapus';
    echo json_encode($response);
    exit;
}


// Hapus unit
$delete_sql = "DELETE FROM playstations WHERE no_ps = '$no_ps' AND userx = '$username'";

if ($con->query($delete_sql)) {
    $response['success'] = true;
    $response['message'] = 'Unit berhasil dihapus';
    
    // Log penghapusan (opsional)
    $log_sql = "INSERT INTO log_activities (userx, action, detail, created_at) 
                VALUES ('$username', 'delete_unit', 'Deleted unit: $no_ps', NOW())";
    $con->query($log_sql);
} else {
    $response['error'] = 'Database error: ' . $con->error;
}

echo json_encode($response);
?>
