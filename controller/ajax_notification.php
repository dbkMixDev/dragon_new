<?php
session_start();
include '../include/config.php';

$username = $_SESSION['username'] ?? '';

$sql = "SELECT id, userx, title, message, created_at FROM notifications WHERE userx = ? ORDER BY created_at DESC LIMIT 10";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

$notifications = [];

$stmt->bind_result($id, $userx, $title, $message, $created_at);
while ($stmt->fetch()) {
    $notifications[] = [
        'id'         => $id,
        'userx'      => $userx,
        'title'      => $title,
        'message'    => $message,
        'created_at' => $created_at
    ];
}

$stmt->close();

header('Content-Type: application/json');
echo json_encode($notifications);
?>
