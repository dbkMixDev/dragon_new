<?php
header('Content-Type: application/json');
require '../include/config.php'; // koneksi DB
session_start();
$username = $_SESSION['username'] ;
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

$query = "SELECT no_ps, 
       COALESCE(start_time, '') AS start_time, 
       COALESCE(end_time, '') AS end_time, 
       status, type_ps,
       duration 
FROM playstations
where userx = '$username'
";

if ($status === '1') {
    $query .= " AND status = 'available'";
} elseif ($status === '0') {
    $query .= " AND status = 'occupied'";
}
// kalau 'all', query tetap tanpa filter

$result = mysqli_query($con, $query);

if (!$result) {
    // Jika query error, kembalikan pesan error
    echo json_encode([
        'error' => mysqli_error($con)
    ]);
    exit;
}

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>
