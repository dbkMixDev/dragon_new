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

    // Cek apakah inv masih NULL
    $stmt_check = $con->prepare("SELECT id_fnb FROM tb_trans_fnb WHERE id_fnb = ? AND inv IS NULL AND userx = ?");
    $stmt_check->bind_param('is', $id, $username);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Produk ini tidak dapat dihapus karena sedang dalam transaksi.']);
        exit;
    }

    // Lanjutkan DELETE
    $stmt = $con->prepare("DELETE FROM tb_fnb WHERE id = ? AND userx = ?");
    $stmt->bind_param('is', $id, $username);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Gagal menghapus data']);
    }

    $stmt->close();
    $stmt_check->close();
    $con->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Metode request tidak valid']);
}
