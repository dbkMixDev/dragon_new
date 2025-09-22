<?php
session_start();
header('Content-Type: application/json');
include '../include/config.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$username = $_SESSION['username'];
$response = ['success' => false];

if (!isset($_POST['no_ps_old']) || !isset($_POST['no_ps']) || !isset($_POST['type_rental']) || 
    !isset($_POST['type_ps']) || !isset($_POST['type_modul']) || !isset($_POST['id_usb'])) {
    $response['error'] = 'Data tidak lengkap';
    echo json_encode($response);
    exit;
}

$no_ps_old = mysqli_real_escape_string($con, $_POST['no_ps_old']);
$no_ps = mysqli_real_escape_string($con, $_POST['no_ps']);
$type_rental = mysqli_real_escape_string($con, $_POST['type_rental']);
$type_ps = mysqli_real_escape_string($con, $_POST['type_ps']);
$type_modul = strtoupper(mysqli_real_escape_string($con, $_POST['type_modul']));
$id_usb = mysqli_real_escape_string($con, $_POST['id_usb']);

// Cek apakah unit milik user
$check_sql = "SELECT type_modul FROM playstations WHERE no_ps = '$no_ps_old' AND userx = '$username'";
$check_result = $con->query($check_sql);

if ($check_result->num_rows == 0) {
    $response['error'] = 'Unit tidak ditemukan atau Anda tidak memiliki akses';
    echo json_encode($response);
    exit;
}

$row = $check_result->fetch_assoc();
$old_type_modul = strtoupper($row['type_modul']);

// Jika no_ps berubah, pastikan tidak duplikat
if ($no_ps != $no_ps_old) {
    $check_duplicate = "SELECT * FROM playstations WHERE no_ps = '$no_ps' AND userx = '$username'";
    $duplicate_result = $con->query($check_duplicate);
    if ($duplicate_result->num_rows > 0) {
        $response['error'] = 'No Unit sudah digunakan';
        echo json_encode($response);
        exit;
    }
} 

// Cek kuota maksimal jika berubah ke ANDROID TV / GOOGLE TV
if (in_array($type_modul, ['ANDROID TV', 'GOOGLE TV']) && !in_array($old_type_modul, ['ANDROID TV', 'GOOGLE TV'])) {
    // Hitung jumlah unit ANDROID TV / GOOGLE TV yang sudah ada
    $count_sql = "SELECT COUNT(*) as total FROM playstations WHERE userx = '$username' AND (type_modul = 'ANDROID TV' OR type_modul = 'GOOGLE TV')";
    $count_result = $con->query($count_sql);
    $count_data = $count_result->fetch_assoc();
    $current_total = $count_data['total'];

    // Ambil batas maksimal dari tb_package
    $limit_sql = "SELECT unit FROM tb_package WHERE username = '$username'";
    $limit_result = $con->query($limit_sql);
    $limit_data = $limit_result->fetch_assoc();
    $max_unit = (int) $limit_data['unit'];

    if ($current_total >= $max_unit) {
        $response['error'] = 'Batas maksimum unit ANDROID TV / GOOGLE TV telah tercapai';
        echo json_encode($response);
        exit;
    }
} else {
    // Jika type_modul selain ANDROID TV atau GOOGLE TV, cek apakah id_usb sudah digunakan oleh siapapun (user manapun)
    $sql_check_usb = $con->prepare("SELECT 1 FROM playstations WHERE id_usb = ? AND no_ps != ? AND userx = ?");
    $sql_check_usb->bind_param('sss', $id_usb,$no_ps_old,$username);
    $sql_check_usb->execute();
    $sql_check_usb->store_result();

    // Jika id_usb sudah digunakan oleh user lain, return error
    if ($sql_check_usb->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'ID Relay sudah digunakan']);
        $sql_check_usb->close();
        exit;
    }
    $sql_check_usb->close();
}


// Update data
$update_sql = "UPDATE playstations SET 
                no_ps = '$no_ps',
                type_rental = '$type_rental',
                type_ps = '$type_ps',
                type_modul = '$type_modul',
                id_usb = '$id_usb'
              WHERE no_ps = '$no_ps_old' AND userx = '$username'";

if ($con->query($update_sql)) {
    $response['success'] = true;
    $response['message'] = 'Unit berhasil diupdate';
} else {
    $response['error'] = 'Database error: ' . $con->error;
}

echo json_encode($response);
?>
