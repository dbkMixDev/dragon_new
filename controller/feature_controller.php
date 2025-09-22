
<?php
// Controller logic (taruh di controller/feature_controller.php)
session_start();
    include('../include/config.php');

if ($_POST['action'] == 'toggle_autoprint') {
    $status = $_POST['status'];
    $username = $_SESSION['username'];
    $feature = $_POST['feature'];
    if ($status == '1') {
        // Enable: INSERT
        $stmt = $con->prepare("INSERT INTO tb_feature (userx, feature, status) VALUES (?, '$feature', 1) 
                              ON DUPLICATE KEY UPDATE status = 1");
        $stmt->bind_param("s", $username);
    } else {
        // Disable: DELETE
        $stmt = $con->prepare("DELETE FROM tb_feature WHERE userx = ? AND feature = '$feature'");
        $stmt->bind_param("s", $username);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}

?>