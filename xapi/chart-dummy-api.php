<?php
header('Content-Type: application/json');

require '../include/config.php';
session_start();
$username = $_SESSION['username'] ?? null;
if (!$username) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userid = $_SESSION['user_id'];

$resX = $con->prepare("SELECT timezone FROM userx WHERE username = ?");
$resX->bind_param("s", $userid);
$resX->execute();
$resXult = $resX->get_result();
if ($row = $resXult->fetch_assoc()) {
    $timezone = $row['timezone'] ?: $defaultTimezone;
}
$resX->close();

date_default_timezone_set($timezone);
$now = date('Y-m-d H:i:s'); // Contoh: 2025-06-14 08:15:00

$data = [];
$totals = [
    'rent' => 0,
    'fnb' => 0,
    'spending' => 0,
    'net' => 0,
];

$max = [
    'rent' => ['value' => 0, 'date' => null],
    'fnb' => ['value' => 0, 'date' => null],
    'spending' => ['value' => 0, 'date' => null],
    'net' => ['value' => null, 'date' => null]
];

$min = [
    'rent' => ['value' => PHP_INT_MAX, 'date' => null],
    'fnb' => ['value' => PHP_INT_MAX, 'date' => null],
    'spending' => ['value' => PHP_INT_MAX, 'date' => null],
    'net' => ['value' => null, 'date' => null]
];

for ($i = 6; $i >= 0; $i--) {
    $tanggal = date('Y-m-d', strtotime("-$i days"));
    $tanggal_label = date('d M', strtotime($tanggal));

    // RENT
    $rent_query = mysqli_query($con, "
        SELECT SUM(harga) AS total
        FROM tb_trans
        WHERE DATE(date_trans) = '$tanggal' AND userx = '$username' AND (is_deleted IS NULL OR is_deleted != 1)
    ");
    $rent_row = mysqli_fetch_assoc($rent_query);
    $rent = (int) ($rent_row['total'] ?? 0);

    // FNB
    $fnb_query = mysqli_query($con, "
        SELECT SUM(total) AS total
        FROM tb_trans_fnb
        WHERE DATE(created_at) = '$tanggal' AND userx = '$username' AND (is_deleted IS NULL OR is_deleted != 1)
    ");
    $fnb_row = mysqli_fetch_assoc($fnb_query);
    $fnb = (int) ($fnb_row['total'] ?? 0);

    // SPENDING
    $spending_query = mysqli_query($con, "
        SELECT SUM(grand_total) AS total
        FROM tb_trans_out
        WHERE DATE(created_at) = '$tanggal' AND userx = '$username' AND (is_deleted IS NULL OR is_deleted != 1)
    ");
    $spending_row = mysqli_fetch_assoc($spending_query);
    $spending = (int) ($spending_row['total'] ?? 0);

    // Net Total = (rent + fnb) - spending
    $net = ($rent + $fnb) - $spending;

    // Tambahkan ke total
    $totals['rent'] += $rent;
    $totals['fnb'] += $fnb;
    $totals['spending'] += $spending;
    $totals['net'] += $net;

    // Cari max
    if ($rent > $max['rent']['value']) {
        $max['rent'] = ['value' => $rent, 'date' => $tanggal_label];
    }
    if ($fnb > $max['fnb']['value']) {
        $max['fnb'] = ['value' => $fnb, 'date' => $tanggal_label];
    }
    if ($spending > $max['spending']['value']) {
        $max['spending'] = ['value' => $spending, 'date' => $tanggal_label];
    }
    if ($max['net']['value'] === null || $net > $max['net']['value']) {
        $max['net'] = ['value' => $net, 'date' => $tanggal_label];
    }

    // Cari min (hanya jika nilai > 0)
    if ($rent > 0 && ($min['rent']['value'] === PHP_INT_MAX || $rent < $min['rent']['value'])) {
        $min['rent'] = ['value' => $rent, 'date' => $tanggal_label];
    }
    if ($fnb > 0 && ($min['fnb']['value'] === PHP_INT_MAX || $fnb < $min['fnb']['value'])) {
        $min['fnb'] = ['value' => $fnb, 'date' => $tanggal_label];
    }
    if ($spending > 0 && ($min['spending']['value'] === PHP_INT_MAX || $spending < $min['spending']['value'])) {
        $min['spending'] = ['value' => $spending, 'date' => $tanggal_label];
    }
    if ($net > 0 && ($min['net']['value'] === null || $net < $min['net']['value'])) {
        $min['net'] = ['value' => $net, 'date' => $tanggal_label];
    }

    // Gabung per hari
    $data[] = [
        'date' => $tanggal_label,
        'rent' => $rent,
        'fnb' => $fnb,
        'spending' => $spending,
        'net' => $net,
    ];
}

// Normalisasi jika tidak ada data
foreach (['rent', 'fnb', 'spending'] as $key) {
    if ($min[$key]['value'] === PHP_INT_MAX || $min[$key]['value'] === null) {
        $min[$key] = ['value' => 0, 'date' => null];
    }
}

if ($min['net']['value'] === null) {
    $min['net'] = ['value' => 0, 'date' => null];
}

echo json_encode([
    'data' => $data,
    'summary' => [
        'total' => $totals,
        'max' => $max,
        'min' => $min
    ]
]);
