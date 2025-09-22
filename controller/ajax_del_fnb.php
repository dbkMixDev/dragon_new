<?php
include '../include/config.php';
header('Content-Type: application/json');
session_start();
$username = $_SESSION['username'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'])) {
        echo json_encode(['success' => false, 'error' => 'ID tidak dikirim']);
        exit;
    }

    $id = intval($_POST['id']);

    // Ambil id_trans dan id_ps sebelum menghapus
    $stmt = $con->prepare("SELECT tf.id_trans, t.id_ps FROM tb_trans_fnb tf 
                          LEFT JOIN tb_trans t ON tf.id_trans = t.id_trans 
                          WHERE tf.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Data tidak ditemukan']);
        $stmt->close();
        exit;
    }

    $stmt->bind_result($id_trans, $id_ps);
    $stmt->fetch();
    $stmt->close();

    // Jika tidak dapat id_ps dari join, coba ambil dari tb_trans_fnb langsung
    if (!$id_ps) {
        $stmt = $con->prepare("SELECT id_ps FROM tb_trans_fnb WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id_ps);
        $stmt->fetch();
        $stmt->close();
    }

    // Hapus item
    $stmt = $con->prepare("DELETE FROM tb_trans_fnb WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $stmt->close();

        // Hitung total FNB
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

        $total_fnb = (int) $total_fnb;

        // PERBAIKAN: Ambil SEMUA harga rental untuk PS ini yang belum di-invoice
        $total_rental = 0;
        if ($id_ps && $username) {
            $stmt = $con->prepare("SELECT SUM(harga) AS total_rental FROM tb_trans 
                                  WHERE id_ps = ? AND inv IS NULL AND userx = ?");
            $stmt->bind_param("is", $id_ps, $username);
            $stmt->execute();
            $stmt->bind_result($total_rental);
            $stmt->fetch();
            $stmt->close();
            
            $total_rental = (int) ($total_rental ?? 0);
        }

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
?>