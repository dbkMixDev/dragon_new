<?php

header('Content-Type: application/json');
session_start();
include '../include/config.php';
$username = $_SESSION['username'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
        exit;
    }

    $stmt = $con->prepare("DELETE FROM tb_pricelist WHERE id = ? And userx = ?");
    $stmt->bind_param('is', $id,$username);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete pricelist']);
    }
    $stmt->close();
    $con->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
