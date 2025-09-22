<?php
// header('Content-Type: application/json');
session_start();
include '../include/config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action'])) {
        if ($data['action'] === 'update') {
            $id = $data['id'];
            $phone = $data['phone'];

            $sql = "UPDATE booking_phone SET phone = ? WHERE id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("si", $phone, $id);
            $result = $stmt->execute();

            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            exit;
        } elseif ($data['action'] === 'add') {
            $phone = $data['phone'];
            $username = $_SESSION['username']; // atau cara lain get username

            $sql = "INSERT INTO booking_phone (phone, email, status) VALUES (?, ?, 'success')";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ss", $phone, $username);
            $result = $stmt->execute();

            header('Content-Type: application/json');
            echo json_encode(['success' => $result, 'id' => $con->insert_id]);
            exit;
        }
    }
}
?>