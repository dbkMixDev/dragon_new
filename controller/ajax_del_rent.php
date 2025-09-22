<?php
include '../include/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'])) {
        echo json_encode(['success' => false, 'error' => 'ID tidak dikirim']);
        exit;
    }

    $id = intval($_POST['id']);

    // Ambil id_trans sebelum menghapus
    $stmt = $con->prepare("SELECT id_trans FROM tb_trans WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Data tidak ditemukan']);
        $stmt->close();
        exit;
    }

    $stmt->bind_result($id_trans);
    $stmt->fetch();
    $stmt->close();

    // Hapus item dari tb_trans
    $stmt = $con->prepare("DELETE FROM tb_trans WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $stmt->close();

        // Hitung total FNB
        $total_fnb = 0;
        $stmt = $con->prepare("
            SELECT SUM(tf.qty * f.harga) AS total 
            FROM tb_trans_fnb tf
            JOIN tb_fnb f ON tf.id_fnb = f.id
            WHERE tf.id_trans = ?
        ");
        $stmt->bind_param("s", $id_trans);
        $stmt->execute();
        $stmt->bind_result($total_fnb);
        $stmt->fetch();
        $stmt->close();

        $total_fnb = (int)$total_fnb;

        // Ambil total rental
        $total_rental = 0;
        $stmt = $con->prepare("SELECT harga FROM tb_trans WHERE id_trans = ? LIMIT 1");
        $stmt->bind_param("s", $id_trans);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($total_rental);
            $stmt->fetch();
            $total_rental = (int)$total_rental;
        }
        $stmt->close();

        echo json_encode([
            'success' => true,
            'total_fnb' => $total_fnb,
            'total_rental' => $total_rental,
            'grand_total' => $total_fnb + $total_rental
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
        $stmt->close();
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Metode harus POST']);
}
