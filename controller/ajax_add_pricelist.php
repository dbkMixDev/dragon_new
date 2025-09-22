<?php

header('Content-Type: application/json');
session_start();
include '../include/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_ps = $_POST['type_ps'] ?? '';
    $duration = intval($_POST['duration'] ?? 0);
    $price = intval($_POST['price'] ?? 0);
    $username = $_SESSION['username'] ?? '';

    if (!$type_ps || $duration <= 0 || $price < 0) {
        echo json_encode(['success' => false, 'error' => 'Input tidak valid']);
        exit;
    }

    // Cek apakah kombinasi type_ps, duration, dan userx sudah ada
    $check = $con->prepare("SELECT id FROM tb_pricelist WHERE type_ps = ? AND duration = ? AND userx = ?");
    $check->bind_param('sis', $type_ps, $duration, $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Durasi sudah terdaftar untuk tipe PS ini.']);
        $check->close();
        $con->close();
        exit;
    }
    $check->close();

    // Insert ke tb_pricelist
    $stmt = $con->prepare("INSERT INTO tb_pricelist (type_ps, duration, price, userx, updated_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('siiss', $type_ps, $duration, $price, $username, $username);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Gagal menambahkan pricelist: ' . $stmt->error]);
    }

    $stmt->close();
    $con->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
