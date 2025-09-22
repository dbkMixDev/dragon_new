<?php
include '../include/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    $stmt = $con->prepare("DELETE FROM tb_trans_fnb WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    $stmt->close();
} else {
    echo 'invalid';
}
?>
