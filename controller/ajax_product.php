<?php

session_start();
include '../include/config.php';

header('Content-Type: application/json');


$userx = isset($_POST['userx']) ? trim($_POST['userx']) : $username;
if (empty($userx)) $userx = $username;

// Validasi & Sanitasi input
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$new_price_raw = isset($_POST['harga']) ? trim($_POST['harga']) : '';

// Bersihkan input harga: hapus titik/koma jika dikirim sebagai format lokal
$new_price_cleaned = preg_replace('/[^\d]/', '', $new_price_raw);
$new_price = is_numeric($new_price_cleaned) ? (int)$new_price_cleaned : 0;

// Debug log (optional, bisa dihapus di production)
// error_log("product update attempt - ID: $id, Price: $new_price, Session User: $username, UserX: $userx");

if ($id <= 0 || $new_price < 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Data tidak valid. ID dan harga harus berupa angka positif.',
        'debug' => [
            'id' => $id,
            'price_input' => $new_price_raw,
            'cleaned_price' => $new_price,
            'session_username' => $username,
            'userx' => $userx
        ]
    ]);
    exit;
}

try {
    // Ambil harga lama
    $stmt_old = $con->prepare("SELECT * FROM tb_fnb WHERE id = ?");
    $stmt_old->bind_param("i", $id);
    $stmt_old->execute();
    $result_old = $stmt_old->get_result();

    if ($result_old->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Data product tidak ditemukan.'
        ]);
        exit;
    }

    $old_data = $result_old->fetch_assoc();
    $old_price = (int)$old_data['harga'];

    if ($old_price === $new_price) {
        echo json_encode([
            'success' => true,
            'message' => 'Harga tidak berubah.',
            'old_price' => $old_price,
            'new_price' => $new_price,
            'session_username' => $username,
            'userx' => $userx,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    // Update harga
    $stmt_update = $con->prepare("UPDATE tb_fnb SET harga = ? , update_at = NOW() WHERE id = ?");
    $stmt_update->bind_param("ii", $new_price,  $id);
    $stmt_update->execute(); // ← BARIS INI PENTING!
     echo json_encode([
            'success' => true,
            'message' => 'Harga berhasil diupdate.',
            'old_price' => $old_price,
            'new_price' => $new_price,
            'session_username' => $username,
            'userx' => $userx,
            'updated_at' => date('Y-m-d H:i:s'),
            
        ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt_old)) $stmt_old->close();
    if (isset($stmt_update)) $stmt_update->close();
    // if (isset($stmt_log)) $stmt_log->close();
}
?>
