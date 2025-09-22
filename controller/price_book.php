<?php
// get_price.php
header('Content-Type: application/json');
include '../include/config.php'; // sesuaikan path

$type = isset($_POST['type']) ? $_POST['type'] : '';
$no_ps = isset($_POST['no_ps']) ? $_POST['no_ps'] : '';

if (empty($type) || empty($no_ps)) {
    echo json_encode(['success' => false, 'message' => 'Input tidak valid']);
    exit;
}

try {
    // Ambil type_ps dari tabel playstations berdasarkan nomor PS
    $sqlPS = "SELECT type_ps FROM playstations WHERE no_ps = '$no_ps' LIMIT 1";
    $resPS = $con->query($sqlPS);

    if ($resPS->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'PS tidak ditemukan']);
        exit;
    }

    $rowPS = $resPS->fetch_assoc();
    $type_ps = $rowPS['type_ps'];

    // Ambil harga dari pricelist berdasarkan type_ps
    $sqlPrice = "SELECT price FROM tb_pricelist WHERE type_ps = '$type_ps' LIMIT 1";
    $resPrice = $con->query($sqlPrice);

    if ($resPrice->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Harga tidak ditemukan']);
        exit;
    }

    $rowPrice = $resPrice->fetch_assoc();
    $price = floatval($rowPrice['price']);

    echo json_encode([
        'success' => true,
        'price' => $price
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
