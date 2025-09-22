<?php

header('Content-Type: application/json');
session_start();
include '../include/config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_ps = $_POST['type_ps'] ?? '';
    $duration = $_POST['duration'];
    $price = intval($_POST['price'] ?? 0);
    $username = $_SESSION['username'] ?? '';


    if (!$type_ps || $price < 0) {
        echo json_encode(['success' => false, 'error' => 'Input tidak valid']);
        exit;
    }

    // Insert ke tb_pricelist
    $stmt = $con->prepare("INSERT INTO tb_fnb (type_fnb, nama, harga,userx) VALUES (?, ?, ?,?)");
    $stmt->bind_param('ssis', $type_ps, $duration, $price,$username);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add product: ' . $stmt->error]);
    }
    $stmt->close();
    $con->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
