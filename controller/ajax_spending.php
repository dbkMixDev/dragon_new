<?php
include '../include/config.php';
session_start();
$userid = $_SESSION['user_id'];
$userx = $_SESSION['username'];
$metode = $_POST['metode_pembayaran'];
$grand_total = $_POST['grand_total'];
$note = $_POST['note'];
$category = $_POST['category'];
$invoice = 'INV' . time();
$bayar = 0;
$kembali = 0;
$promo = 0;
$created_at = $_POST['datetimes'];
$id_trans = 'TRX-OUT' . substr(md5(uniqid()), 0, 8);

// Insert into tb_trans_out using prepared statement
$stmt1 = $con->prepare("INSERT INTO tb_trans_out 
                        (metode_pembayaran, created_at, id_trans, userx, grand_total, category, note, invoice,usercreate)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)");
$stmt1->bind_param("ssssissss", $metode, $created_at, $id_trans, $userx, $grand_total, $category, $note, $invoice,$userid);

// Insert into tb_trans_final using prepared statement
$stmt2 = $con->prepare("INSERT INTO tb_trans_final 
                        (metode_pembayaran, bayar, kembali, promo, invoice, id_trans, userx, grand_total, created_at,usercreate)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
$stmt2->bind_param("siiisssiss", $metode, $bayar, $kembali, $promo, $invoice, $id_trans, $userx, $grand_total, $created_at,$userid);

// Execute both statements
$success1 = $stmt1->execute();
$success2 = $stmt2->execute();

if ($success1 && $success2) {
    echo 'ok';
} else {
    $error_msg = '';
    if (!$success1) {
        $error_msg .= 'Error tb_trans_out: ' . $stmt1->error . ' ';
    }
    if (!$success2) {
        $error_msg .= 'Error tb_trans_final: ' . $stmt2->error;
    }
    echo 'error: ' . $error_msg;
}

// Close statements
$stmt1->close();
$stmt2->close();
?>
