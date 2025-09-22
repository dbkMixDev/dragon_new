<?php
session_start();
include '../include/config.php';
 $username = $_SESSION['username'] ?? '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userx = $_SESSION['username'] ?? 'System';
    $psNumber = $_POST['psNumber'];
    $title = "PlayStation #$psNumber";
    $message = "PlayStation #$psNumber: 5 minutes remaining!";
    $icon = 'bx-time';
    $image_url = null;

    $stmt = $con->prepare("INSERT INTO notifications (userx, title, message, icon, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $userx, $title, $message, $icon, $image_url);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

    $stmt->close();
}
?>
