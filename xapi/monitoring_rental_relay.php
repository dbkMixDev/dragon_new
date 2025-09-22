<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../include/config.php'; // koneksi ke DB

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Ambil ID USB dari query string (unik untuk tiap perangkat)
$id_usb = $_GET['id_usb'] ?? null;
$ip = $_GET['ip'] ?? null;
if (!$id_usb) {
    echo json_encode(['error' => 'Missing id_usb'], JSON_PRETTY_PRINT);
    exit;
}

// Ambil informasi unit berdasarkan id_usb
$sql = "
    SELECT * FROM playstations 
    WHERE id_usb = '$id_usb' 
    AND type_modul NOT IN ('ANDROID TV', 'GOOGLE TV') 
    LIMIT 1
";

$result = $con->query($sql);
if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Device not found'], JSON_PRETTY_PRINT);
    exit;
}

$row = $result->fetch_assoc();
$username  = $row['userx'];
$unit_no   = $row['no_ps'];
$status    = $row['status'];
$duration  = $row['duration'];
$start_raw = $row['start_time'];
$stop_raw  = $row['end_time'];

$timezone  = 'Asia/Jakarta';

// Ambil timezone user jika ada
$ruser = $con->query("SELECT timezone FROM userx WHERE username = '$username' LIMIT 1");
if ($ruser && $ruser->num_rows > 0) {
    $tz = $ruser->fetch_assoc();
    $timezone = $tz['timezone'] ?: $timezone;
}
date_default_timezone_set($timezone);
$now = time();

// Konversi ke UNIX timestamp dengan timezone yang sudah diset
$start = strtotime($start_raw);
$stop  = strtotime($stop_raw);

// Update status_device (ping)
$current_time = date('Y-m-d H:i:s');
$con->query("UPDATE playstations SET status_device = '$current_time', ip_relay = '$ip' WHERE id_usb = '$id_usb'");

// Hitung sisa waktu dalam MILIDETIK
$remaining = max(0, ($stop - $now) * 1000);
if($duration == "open") {
    $remaining = 99999; // Pastikan durasi tidak negatif
}
// Response output
$response = [
    'unit_no'       => (int)$unit_no,
    'ip'            => $id_usb,
    'start'         => $row['start_time'],
    'stop'          => $row['end_time'],
    'status'        => $status,
    'on_layar'      => ($status === 'available') ? 0 : 1,
    'remaining'     => $remaining, // Waktu remaining dalam MILIDETIK
    'type_modul'    => $row['type_modul'] ?? 'unknown',
    'status_device' => $current_time,
    'ping_status'   => 'online',
    'last_update'   => date('Y-m-d H:i:s'),
];

echo json_encode($response, JSON_PRETTY_PRINT);
$con->close();
?>
