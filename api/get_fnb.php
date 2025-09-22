<?php
// Endpoint untuk ambil data FnB (tb_fnb)
require_once '../include/config.php';
header('Content-Type: application/json');

$res = $con->query("SELECT * FROM tb_fnb ORDER BY nama ASC");
$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
