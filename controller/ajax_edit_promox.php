<?php
include '../include/config.php';

// Ambil data dari POST
$id         = $_POST['id'];
$nama       = $_POST['nama_promo'];
$type       = $_POST['type_rental'];
$potongan   = $_POST['qty_potongan'];
$disc_type  = $_POST['disc_type'];

// Gunakan prepared statement untuk keamanan
$stmt = $con->prepare("UPDATE tb_promo 
                       SET nama_promo = ?, type_rental = ?, qty_potongan = ?, disc_type = ? 
                       WHERE id = ?");
$stmt->bind_param("ssiss", $nama, $type, $potongan, $disc_type, $id);

if ($stmt->execute()) {
    echo 'ok';
} else {
    echo 'error: ' . $stmt->error;
}

$stmt->close();
?>
