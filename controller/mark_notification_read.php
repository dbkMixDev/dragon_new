<?php
session_start();
include '../include/config.php';
if ($_POST['id']) {
    $id = $_POST['id'];
    
    
    // Update status PlayStation menjadi available
    $q = "UPDATE notifications SET is_read= 1 WHERE id='$id'";
    $result1 = mysqli_query($con, $q);
    if ($result1) {
    echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui status notifikasi']);
    }
}
?>