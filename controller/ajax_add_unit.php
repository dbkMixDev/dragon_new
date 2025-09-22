<?php
header('Content-Type: application/json');
session_start();
include '../include/config.php'; // Pastikan koneksi ke database sudah benar

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'User belum login']);
    exit;
}

$username = $_SESSION['username'];

// Ambil data dari POST
$no_ps = isset($_POST['no_ps']) ? trim($_POST['no_ps']) : '';
$type_rental = isset($_POST['type_rental']) ? trim($_POST['type_rental']) : '';
$type_ps = isset($_POST['type_ps']) ? trim($_POST['type_ps']) : '';
$id_usb = isset($_POST['id_usb']) ? trim($_POST['id_usb']) : '';
$type_modul = isset($_POST['type_modul']) ? trim($_POST['type_modul']) : '';

// Validasi jika ada field yang kosong
if ($no_ps === '' || $type_rental === '' || $type_ps === '' || $id_usb === '' || $type_modul === '') {
    echo json_encode(['success' => false, 'error' => 'Semua field harus diisi']);
    exit;
}

// Cek apakah no_ps dan id_usb sudah digunakan oleh user yang sama
$sql_check = $con->prepare("SELECT 1 FROM playstations WHERE userx = ? AND (no_ps = ? OR id_usb = ?)");
$sql_check->bind_param('sss', $username, $no_ps, $id_usb);
$sql_check->execute();
$sql_check->store_result();
if ($sql_check->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'No Unit atau ID USB sudah digunakan oleh Anda']);
    $sql_check->close();
    exit;
}
$sql_check->close();

// Jika type_modul adalah ANDROID TV atau GOOGLE TV, jalankan fungsi untuk cek batas unit
if (in_array(strtoupper($type_modul), ['ANDROID TV', 'GOOGLE TV'])) {
    // Ambil jumlah unit yang sudah didaftarkan oleh user untuk modul tersebut
    $stmt_unit_count = $con->prepare("SELECT COUNT(*) FROM playstations WHERE userx = ? AND (type_modul = 'ANDROID TV' OR type_modul = 'GOOGLE TV')");
    $stmt_unit_count->bind_param('s', $username);
    $stmt_unit_count->execute();
    $stmt_unit_count->bind_result($current_unit);
    $stmt_unit_count->fetch();
    $stmt_unit_count->close();

    // Ambil batas maksimum unit dari tb_package
    $stmt_limit = $con->prepare("SELECT unit FROM tb_package WHERE username = ?");
    $stmt_limit->bind_param('s', $username);
    $stmt_limit->execute();
    $stmt_limit->bind_result($unit_limit);
    $stmt_limit->fetch();
    $stmt_limit->close();

    // Cek apakah unit yang sudah ada melebihi batas
    if ($current_unit >= $unit_limit) {
        echo json_encode(['success' => false, 'error' => 'Batas maksimum unit ANDROID TV / GOOGLE TV telah tercapai']);
        exit;
    }
} else {
    // Jika type_modul selain ANDROID TV atau GOOGLE TV, cek apakah id_usb sudah digunakan oleh siapapun (user manapun)
    $sql_check_usb = $con->prepare("SELECT 1 FROM playstations WHERE id_usb = ?");
    $sql_check_usb->bind_param('s', $id_usb);
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

// Lanjutkan insert data jika semua valid
$status_device = '-'; // Default status device
$sql_insert = $con->prepare("INSERT INTO playstations (no_ps, status_device, type_ps, type_rental, id_usb, type_modul, userx) VALUES (?, ?, ?, ?, ?, ?, ?)");
$sql_insert->bind_param('sssssss', $no_ps, $status_device, $type_ps, $type_rental, $id_usb, $type_modul, $username);

if ($sql_insert->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Gagal menyimpan data']);
}

// Tutup koneksi
$sql_insert->close();
$con->close();
?>
