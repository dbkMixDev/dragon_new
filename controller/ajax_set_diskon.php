<?php
include '../include/config.php';

$id_trans = isset($_POST['id_trans']) ? $_POST['id_trans'] : '';
$diskon = isset($_POST['diskon']) ? (int)$_POST['diskon'] : 0;

if ($id_trans !== '') {
    // Pastikan kolom diskon ada di tb_trans, jika belum tambahkan di database
    mysqli_query($con, "UPDATE tb_trans SET diskon='$diskon' WHERE id_trans='$id_trans'");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'ID transaksi tidak valid']);
}