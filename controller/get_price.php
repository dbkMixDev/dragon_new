<?php
require '../include/config.php';

$type_ps = $_POST['type_ps'] ?? '';
$userx = $_POST['userx'] ?? '';

if (!$type_ps || !$userx) {
    echo json_encode([]);
    exit;
}

$query = "SELECT duration, price FROM tb_pricelist WHERE type_ps = ? AND userx = ? AND price != 0 ORDER BY duration+0 ASC";
$stmt = $con->prepare($query);
$stmt->bind_param("ss", $type_ps, $userx);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
