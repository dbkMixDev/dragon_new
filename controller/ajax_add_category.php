<?php

header('Content-Type: application/json');
session_start();
include '../include/config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name_cat'] ?? '';
    $username = $_SESSION['username'] ?? '';


    if (empty($name) || empty($username)) {
        echo json_encode(['success' => false, 'error' => 'Input tidak valid']);
        exit;
    }

    // Insert ke tb_pricelist
    $stmt = $con->prepare("INSERT INTO tb_category (name,userx) VALUES (?, ?)");
    $stmt->bind_param('ss', $name,$username);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add pricelist: ' . $stmt->error]);
    }
    $stmt->close();
    $con->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
