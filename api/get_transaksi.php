<?php
// Endpoint untuk ambil transaksi utama dan detail untuk 1 PS (by no_ps)
require_once '../include/config.php';
header('Content-Type: application/json');

$no_ps = isset($_GET['no_ps']) ? $_GET['no_ps'] : null;
if (!$no_ps) {
    echo json_encode(['error'=>'no_ps required']);
    exit;
}
// Ambil transaksi utama (id_trans terbaru untuk PS ini)
$res = $con->query("SELECT * FROM tb_trans WHERE id_ps='$no_ps' ORDER BY start ASC");
$transaksi = [];
$id_trans = null;
$userx = null;
while ($row = $res->fetch_assoc()) {
    if (!$id_trans) $id_trans = $row['id_trans'];
    if (!$userx && isset($row['userx'])) $userx = $row['userx'];
    $transaksi[] = [
        'start' => $row['start'],
        'end' => $row['end'],
        'durasi' => $row['durasi'],
        'harga' => $row['harga']
    ];
}
echo json_encode([
    'id_trans' => $id_trans,
    'userx' => $userx,
    'transaksi' => $transaksi
]);
