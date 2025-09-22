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

    // Ambil nama kategori berdasarkan id dan userx
    $stmt = $con->prepare("SELECT name FROM tb_category WHERE id_category = ? AND userx = ?");
    $stmt->bind_param('is', $id, $username);
    $stmt->execute();
    $stmt->bind_result($category_name);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Category not found']);
        $stmt->close();
        $con->close();
        exit;
    }
    $stmt->close();

    // Cek di pricelist apakah ada type_ps yang sama dengan nama kategori
    $stmt = $con->prepare("SELECT COUNT(*) FROM tb_pricelist WHERE type_ps = ? AND userx = ?");
    $stmt->bind_param('ss', $category_name, $username);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo json_encode(['success' => false, 'error' => 'Tidak bisa hapus, ada category di pricelist yang sama dengan nama kategori ini']);
        $con->close();
        exit;
    }

    // Lanjut hapus jika tidak ada di pricelist
    $stmt = $con->prepare("DELETE FROM tb_category WHERE id_category = ? AND userx = ?");
    $stmt->bind_param('is', $id, $username);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete category']);
    }
    $stmt->close();
    $con->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
